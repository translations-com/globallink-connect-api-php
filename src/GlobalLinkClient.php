<?php

declare(strict_types=1);

namespace GlobalLink\RestClient;

use GlobalLink\RestClient\Config\RetryConfig;
use GlobalLink\RestClient\Exception\ApiException;
use GlobalLink\RestClient\Exception\AuthenticationException;
use GlobalLink\RestClient\Exception\GlobalLinkException;
use GlobalLink\RestClient\Exception\InvalidArgumentException;
use GlobalLink\RestClient\Exception\RateLimitException;
use GlobalLink\RestClient\Http\HttpClientInterface;
use GlobalLink\RestClient\Http\HttpResponse;
use GlobalLink\RestClient\Http\StreamHttpClient;
use GlobalLink\RestClient\Model\ProjectInfo;
use GlobalLink\RestClient\Model\SubmissionInfo;
use GlobalLink\RestClient\Model\Target;
use GlobalLink\RestClient\Model\TechTracking;
use GlobalLink\RestClient\Request\CancelSubmissionRequest;
use GlobalLink\RestClient\Request\CreateSubmissionRequest;
use GlobalLink\RestClient\Request\GetSubmissionsRequest;
use GlobalLink\RestClient\Request\GetTargetsRequest;
use GlobalLink\RestClient\Request\ListProjectsRequest;
use GlobalLink\RestClient\Request\LoginRequest;
use GlobalLink\RestClient\Request\SaveSubmissionRequest;
use GlobalLink\RestClient\Request\UploadReferenceFileRequest;
use GlobalLink\RestClient\Request\UploadSourceFileRequest;
use GlobalLink\RestClient\Response\ConfirmTargetDeliveredResponse;
use GlobalLink\RestClient\Response\CreateSubmissionResponse;
use GlobalLink\RestClient\Response\GetProjectCustomAttributesResponse;
use GlobalLink\RestClient\Response\GetProjectFileFormatsResponse;
use GlobalLink\RestClient\Response\GetProjectLanguageDirectionsResponse;
use GlobalLink\RestClient\Response\GetProjectMslaLevelsResponse;
use GlobalLink\RestClient\Response\GetProjectOrgUsersResponse;
use GlobalLink\RestClient\Response\GetProjectWorkflowsResponse;
use GlobalLink\RestClient\Response\GetSubmissionsResponse;
use GlobalLink\RestClient\Response\GetSubmissionWordcountResponse;
use GlobalLink\RestClient\Response\GetTargetsResponse;
use GlobalLink\RestClient\Response\HealthcheckResponse;
use GlobalLink\RestClient\Response\InstanceInfoResponse;
use GlobalLink\RestClient\Response\ListProjectsResponse;
use GlobalLink\RestClient\Response\LoginResponse;
use GlobalLink\RestClient\Response\SaveSubmissionResponse;
use GlobalLink\RestClient\Response\UploadReferenceFileResponse;
use GlobalLink\RestClient\Response\UploadSourceFileResponse;

/**
 * GlobalLink Enterprise REST API Client.
 *
 * This is the main client for interacting with the GlobalLink Enterprise REST API.
 * It provides methods for authentication, project management, submission handling,
 * file uploads, and target retrieval.
 */
class GlobalLinkClient
{
    private const SDK_VERSION = '2.0.0';
    private const CONTENT_TYPE_JSON = 'application/json';
    private const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    private readonly string $baseUrl;
    private readonly string $username;
    private readonly string $password;
    private readonly string $basicAuth;
    private readonly HttpClientInterface $httpClient;
    private readonly string $userAgent;
    private readonly ?RetryConfig $retryConfig;

    private ?string $accessToken = null;
    private ?int $tokenExpiresAt = null;

    /** @var array{limit: ?int, remaining: ?int, reset: ?int}|null */
    private ?array $lastRateLimitInfo = null;

    /**
     * Create a new GlobalLink client.
     *
     * @param string $baseUrl Base URL of the GlobalLink API (e.g., https://example.globallink.com)
     * @param string $username GlobalLink username
     * @param string $password GlobalLink password
     * @param string $oauthWsClient OAuth client ID
     * @param string $oauthWsPassword OAuth client secret
     * @param HttpClientInterface|null $httpClient Optional custom HTTP client (for testing or custom implementations)
     * @param string|null $userAgent Optional custom User-Agent header
     * @param RetryConfig|null $retryConfig Optional retry configuration for transient failures (429, 5xx)
     * @throws AuthenticationException If initial authentication fails
     */
    public function __construct(
        string $baseUrl,
        string $username,
        #[\SensitiveParameter] string $password,
        string $oauthWsClient,
        #[\SensitiveParameter] string $oauthWsPassword,
        ?HttpClientInterface $httpClient = null,
        ?string $userAgent = null,
        ?RetryConfig $retryConfig = null,
    ) {
        // Validate required parameters
        if (empty(trim($baseUrl))) {
            throw new InvalidArgumentException('Base URL cannot be empty');
        }
        if (empty($username)) {
            throw new InvalidArgumentException('Username cannot be empty');
        }
        if (empty($password)) {
            throw new InvalidArgumentException('Password cannot be empty');
        }
        if (empty($oauthWsClient)) {
            throw new InvalidArgumentException('OAuth client ID cannot be empty');
        }
        if (empty($oauthWsPassword)) {
            throw new InvalidArgumentException('OAuth client secret cannot be empty');
        }

        $this->baseUrl = rtrim($baseUrl, '/');
        $this->username = $username;
        $this->password = $password;
        $this->basicAuth = base64_encode($oauthWsClient . ':' . $oauthWsPassword);
        $this->httpClient = $httpClient ?? new StreamHttpClient();
        $this->userAgent = $userAgent ?? sprintf(
            'globallink-connect-php/%s (PHP/%s)',
            self::SDK_VERSION,
            PHP_VERSION
        );
        $this->retryConfig = $retryConfig;

        // Authenticate on construction
        $this->authenticate();
    }

    /**
     * Check if the current access token is expired.
     */
    public function isTokenExpired(): bool
    {
        if ($this->accessToken === null || $this->tokenExpiresAt === null) {
            return true;
        }

        // Add a 60-second buffer to account for clock skew
        return time() >= ($this->tokenExpiresAt - 60);
    }

    /**
     * Get the last seen rate limit information.
     *
     * @return array{limit: ?int, remaining: ?int, reset: ?int}|null
     */
    public function getRateLimitInfo(): ?array
    {
        return $this->lastRateLimitInfo;
    }

    // =========================================================================
    // Projects
    // =========================================================================

    /**
     * Get a project by ID.
     *
     * @param string|int $projectId Project ID
     * @return ProjectInfo Detailed project information
     * @throws ApiException On API error
     */
    public function getProject(string|int $projectId): ProjectInfo
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($projectId);

        $result = $this->request('GET', "/rest/v0/projects/{$safeId}");
        return ProjectInfo::fromJson($result['body']);
    }

    /**
     * List all projects with optional filters.
     *
     * @param ListProjectsRequest|null $request Optional filters
     * @return list<\GlobalLink\RestClient\Model\Project> List of projects
     * @throws ApiException On API error
     */
    public function getProjects(?ListProjectsRequest $request = null): array
    {
        $this->ensureAuthenticated();

        $queryParams = $request?->toQueryParams() ?? [];
        $result = $this->request('GET', '/rest/v0/projects', ['query' => $queryParams]);

        return ListProjectsResponse::fromJson($result['body']);
    }

    /**
     * Get file format profiles for a project.
     *
     * @param int $projectId Project ID
     * @return list<\GlobalLink\RestClient\Model\FileFormatProfile>
     * @throws ApiException On API error
     */
    public function getProjectFileFormats(int $projectId): array
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($projectId);

        $result = $this->request('GET', "/rest/v0/projects/{$safeId}/fileformats");
        return GetProjectFileFormatsResponse::fromJson($result['body']);
    }

    /**
     * Get workflow definitions for a project.
     *
     * @param int $projectId Project ID
     * @return list<\GlobalLink\RestClient\Model\WorkflowDefinition>
     * @throws ApiException On API error
     */
    public function getProjectWorkflows(int $projectId): array
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($projectId);

        $result = $this->request('GET', "/rest/v0/projects/{$safeId}/workflows");
        return GetProjectWorkflowsResponse::fromJson($result['body']);
    }

    /**
     * Get custom attribute definitions for a project.
     *
     * @param int $projectId Project ID
     * @return list<\GlobalLink\RestClient\Model\CustomAttribute>
     * @throws ApiException On API error
     */
    public function getProjectCustomAttributes(int $projectId): array
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($projectId);

        $result = $this->request('GET', "/rest/v0/projects/{$safeId}/customattributes");
        return GetProjectCustomAttributesResponse::fromJson($result['body']);
    }

    /**
     * Get organization users for a project.
     *
     * @param int $projectId Project ID
     * @return list<\GlobalLink\RestClient\Model\User>
     * @throws ApiException On API error
     */
    public function getProjectOrgUsers(int $projectId): array
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($projectId);

        $result = $this->request('GET', "/rest/v0/projects/{$safeId}/users/org");
        return GetProjectOrgUsersResponse::fromJson($result['body']);
    }

    /**
     * Get language directions for a project.
     *
     * @param int $projectId Project ID
     * @return list<\GlobalLink\RestClient\Model\ProjectLanguageDirection>
     * @throws ApiException On API error
     */
    public function getProjectLanguageDirections(int $projectId): array
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($projectId);

        $result = $this->request('GET', "/rest/v0/projects/{$safeId}/languagedirections");
        return GetProjectLanguageDirectionsResponse::fromJson($result['body']);
    }

    /**
     * Get MSLA (priority) levels for a project.
     *
     * MSLA levels define submission priorities (e.g., Rush, Standard, Low).
     *
     * @param int $projectId Project ID
     * @return list<\GlobalLink\RestClient\Model\MslaLevel>
     * @throws ApiException On API error
     */
    public function getProjectMslaLevels(int $projectId): array
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($projectId);

        $result = $this->request('GET', "/rest/v0/projects/{$safeId}/mslalevels");
        return GetProjectMslaLevelsResponse::fromJson($result['body']);
    }

    // =========================================================================
    // Instance
    // =========================================================================

    /**
     * Get instance information (version, limits, etc.).
     *
     * @return InstanceInfoResponse Instance information
     * @throws ApiException On API error
     */
    public function getInstanceInfo(): InstanceInfoResponse
    {
        $this->ensureAuthenticated();

        $result = $this->request('GET', '/rest/v0/instanceInfo');
        return InstanceInfoResponse::fromJson($result['body']);
    }

    /**
     * Check if the GLE instance is healthy and available.
     *
     * This endpoint can be used to verify server availability before making
     * other API calls. Recommended for use after repeated connection failures.
     *
     * @return HealthcheckResponse Health status
     */
    public function healthcheck(): HealthcheckResponse
    {
        $headers = [
            'User-Agent' => $this->userAgent,
        ];

        $response = $this->httpClient->send('GET', $this->baseUrl . '/health.pd', $headers, null);

        return HealthcheckResponse::fromResponse($response->body, $response->statusCode);
    }

    // =========================================================================
    // Submissions - CRUD
    // =========================================================================

    /**
     * Create a new submission.
     *
     * @param CreateSubmissionRequest $request Submission details
     * @return CreateSubmissionResponse Response containing the new submission ID
     * @throws ApiException On API error
     */
    public function createSubmission(CreateSubmissionRequest $request): CreateSubmissionResponse
    {
        $this->ensureAuthenticated();

        $result = $this->request('POST', '/rest/v0/submissions/create', ['json' => $request->toArray()]);
        return CreateSubmissionResponse::fromJson($result['body']);
    }

    /**
     * Get submissions with optional filters.
     *
     * @param GetSubmissionsRequest|null $request Optional filters
     * @return list<SubmissionInfo> List of submissions
     * @throws ApiException On API error
     */
    public function getSubmissions(?GetSubmissionsRequest $request = null): array
    {
        $this->ensureAuthenticated();

        $queryParams = $request?->toQueryParams() ?? [];
        $result = $this->request('GET', '/rest/v0/submissions', ['query' => $queryParams]);

        return GetSubmissionsResponse::fromJson($result['body']);
    }

    /**
     * Get a submission by ID.
     *
     * @param int $submissionId Submission ID
     * @return SubmissionInfo Submission details
     * @throws ApiException On API error
     */
    public function getSubmission(int $submissionId): SubmissionInfo
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($submissionId);

        $result = $this->request('GET', "/rest/v0/submissions/{$safeId}");
        return SubmissionInfo::fromJson($result['body']);
    }

    /**
     * Get wordcount information for a submission.
     *
     * Returns detailed wordcount breakdown including leverage categories
     * (100% match, fuzzy matches, no match, etc.).
     *
     * @param int $submissionId Submission ID
     * @return \GlobalLink\RestClient\Model\SubmissionWordcount Wordcount information
     * @throws ApiException On API error
     */
    public function getSubmissionWordcount(int $submissionId): \GlobalLink\RestClient\Model\SubmissionWordcount
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($submissionId);

        $result = $this->request('GET', "/rest/v0/submissions/{$safeId}/wordcount");
        return GetSubmissionWordcountResponse::fromJson($result['body']);
    }

    /**
     * Patch a submission property.
     *
     * @param int $submissionId Submission ID
     * @param string $op Operation (usually "replace")
     * @param string $path Property path (e.g., /name, /instructions, /dueDate)
     * @param mixed $value New value
     * @return SubmissionInfo Updated submission
     * @throws ApiException On API error
     */
    public function patchSubmission(int $submissionId, string $op, string $path, mixed $value): SubmissionInfo
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($submissionId);

        $body = [
            'op' => $op,
            'path' => $path,
            'value' => $value,
        ];

        $result = $this->request('PATCH', "/rest/v0/submissions/{$safeId}", ['json' => $body]);
        return SubmissionInfo::fromJson($result['body']);
    }

    /**
     * Save (finalize) a submission.
     *
     * @param int $submissionId Submission ID
     * @param SaveSubmissionRequest|null $request Optional save options (e.g., autoStart)
     * @return SaveSubmissionResponse Response with started/linked submission IDs
     * @throws ApiException On API error
     */
    public function saveSubmission(int $submissionId, ?SaveSubmissionRequest $request = null): SaveSubmissionResponse
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($submissionId);

        $options = $request?->toArray() ?? [];
        $result = $this->request('POST', "/rest/v0/submissions/{$safeId}/save", ['query' => $options]);

        return SaveSubmissionResponse::fromJson($result['body']);
    }

    /**
     * Cancel a submission.
     *
     * @param int $submissionId Submission ID
     * @param CancelSubmissionRequest $request Cancel request with target or document IDs
     * @return bool True if successful
     * @throws ApiException On API error
     */
    public function cancelSubmission(int $submissionId, CancelSubmissionRequest $request): bool
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($submissionId);

        $result = $this->request('POST', "/rest/v0/submissions/cancel/{$safeId}", ['json' => $request->toArray()]);
        return $result['status'] === 200;
    }

    // =========================================================================
    // Submissions - File Upload
    // =========================================================================

    /**
     * Upload a source file to a submission.
     *
     * @param int $submissionId Submission ID
     * @param UploadSourceFileRequest $request Upload request with file details
     * @return UploadSourceFileResponse Upload response with document IDs
     * @throws ApiException On API error
     * @throws InvalidArgumentException If file is invalid
     */
    public function uploadSubmissionSourceFile(int $submissionId, UploadSourceFileRequest $request): UploadSourceFileResponse
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($submissionId);

        $result = $this->request(
            'POST',
            "/rest/v0/submissions/{$safeId}/upload/source",
            ['multipart' => $request->toMultipart()]
        );

        return UploadSourceFileResponse::fromJson($result['body']);
    }

    /**
     * Upload a reference file to a submission.
     *
     * @param int $submissionId Submission ID
     * @param UploadReferenceFileRequest $request Upload request with file details
     * @return UploadReferenceFileResponse Upload response
     * @throws ApiException On API error
     * @throws InvalidArgumentException If file is invalid
     */
    public function uploadSubmissionReferenceFile(int $submissionId, UploadReferenceFileRequest $request): UploadReferenceFileResponse
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($submissionId);

        $result = $this->request(
            'POST',
            "/rest/v0/submissions/{$safeId}/upload/reference",
            ['multipart' => $request->toMultipart()]
        );

        return UploadReferenceFileResponse::fromJson($result['body']);
    }

    // =========================================================================
    // Submissions - Tech Tracking
    // =========================================================================

    /**
     * Set technology tracking information for a submission.
     *
     * @param int $submissionId Submission ID
     * @param TechTracking $techTracking Tech tracking information
     * @return SubmissionInfo Updated submission
     * @throws ApiException On API error
     */
    public function putSubmissionTechTracking(int $submissionId, TechTracking $techTracking): SubmissionInfo
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($submissionId);

        $result = $this->request(
            'PUT',
            "/rest/v0/submissions/{$safeId}/techtracking",
            ['json' => $techTracking->toArray()]
        );

        // The API may return the updated submission or an empty response
        // If we get a valid JSON response with submission data, parse it
        // Otherwise, fetch the submission to get the updated state
        if (!empty($result['body']) && json_validate($result['body'])) {
            $data = json_decode($result['body'], true);
            // Check if it's a submission response (has submissionId)
            if (isset($data['submissionId'])) {
                return SubmissionInfo::fromJson($result['body']);
            }
        }

        // Fetch the submission to return updated state
        return $this->getSubmission($submissionId);
    }

    // =========================================================================
    // Targets - Retrieval
    // =========================================================================

    /**
     * Get targets with filters.
     *
     * @param GetTargetsRequest $request Target query with status and optional filters
     * @return list<Target> List of targets
     * @throws ApiException On API error
     */
    public function getTargets(GetTargetsRequest $request): array
    {
        $this->ensureAuthenticated();

        $result = $this->request('GET', '/rest/v0/targets', ['query' => $request->toArray()]);
        return GetTargetsResponse::fromJson($result['body']);
    }

    // =========================================================================
    // Targets - Download
    // =========================================================================

    /**
     * Download a single target deliverable.
     *
     * @param int $submissionId Submission ID
     * @param int $targetId Target ID
     * @return string File contents
     * @throws ApiException On API error
     */
    public function downloadTargetDeliverable(int $submissionId, int $targetId): string
    {
        $this->ensureAuthenticated();
        $safeSubmissionId = $this->validatePathSegment($submissionId);
        $safeTargetId = $this->validatePathSegment($targetId);

        $result = $this->request(
            'GET',
            "/rest/v0/submissions/{$safeSubmissionId}/targets/{$safeTargetId}/download/deliverable"
        );

        return $result['body'];
    }

    /**
     * Download all deliverables for a submission.
     *
     * @param int $submissionId Submission ID
     * @param bool $downloadAllDeliverables Whether to download all deliverables
     * @param bool|null $includeManifest Whether to include manifest file
     * @return string ZIP file contents
     * @throws ApiException On API error
     */
    public function downloadSubmissionDeliverables(
        int $submissionId,
        bool $downloadAllDeliverables = true,
        ?bool $includeManifest = null,
    ): string {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($submissionId);

        $query = ['downloadAllDeliverables' => $downloadAllDeliverables ? 'true' : 'false'];
        if ($includeManifest !== null) {
            $query['includeManifest'] = $includeManifest ? 'true' : 'false';
        }

        $result = $this->request('GET', "/rest/v0/submissions/{$safeId}/download", ['query' => $query]);
        return $result['body'];
    }

    /**
     * Download only deliverable files for a submission.
     *
     * @param int $submissionId Submission ID
     * @param bool $downloadOnlyDeliverables Whether to download only deliverables
     * @param bool|null $includeManifest Whether to include manifest file
     * @return string ZIP file contents
     * @throws ApiException On API error
     */
    public function downloadSubmissionOnlyDeliverables(
        int $submissionId,
        bool $downloadOnlyDeliverables = true,
        ?bool $includeManifest = null,
    ): string {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($submissionId);

        $query = ['downloadOnlyDeliverables' => $downloadOnlyDeliverables ? 'true' : 'false'];
        if ($includeManifest !== null) {
            $query['includeManifest'] = $includeManifest ? 'true' : 'false';
        }

        $result = $this->request('GET', "/rest/v0/submissions/{$safeId}/download", ['query' => $query]);
        return $result['body'];
    }

    /**
     * Download deliverables for specific languages.
     *
     * @param int $submissionId Submission ID
     * @param list<string> $deliverableLanguages Target language codes (e.g., ['zh-CN', 'fr-FR'])
     * @param bool|null $includeManifest Whether to include manifest file
     * @return string ZIP file contents
     * @throws ApiException On API error
     */
    public function downloadSubmissionDeliverablesByLanguages(
        int $submissionId,
        array $deliverableLanguages,
        ?bool $includeManifest = null,
    ): string {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($submissionId);

        $query = ['deliverableLanguages' => implode(',', $deliverableLanguages)];
        if ($includeManifest !== null) {
            $query['includeManifest'] = $includeManifest ? 'true' : 'false';
        }

        $result = $this->request('GET', "/rest/v0/submissions/{$safeId}/download", ['query' => $query]);
        return $result['body'];
    }

    /**
     * Download deliverables for specific target IDs.
     *
     * @param int $submissionId Submission ID
     * @param list<int> $deliverableTargetIds Target IDs to download
     * @param bool|null $includeManifest Whether to include manifest file
     * @return string ZIP file contents
     * @throws ApiException On API error
     */
    public function downloadSubmissionDeliverablesByTargetIds(
        int $submissionId,
        array $deliverableTargetIds,
        ?bool $includeManifest = null,
    ): string {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($submissionId);

        $query = ['deliverableTargetIds' => implode(',', $deliverableTargetIds)];
        if ($includeManifest !== null) {
            $query['includeManifest'] = $includeManifest ? 'true' : 'false';
        }

        $result = $this->request('GET', "/rest/v0/submissions/{$safeId}/download", ['query' => $query]);
        return $result['body'];
    }

    // =========================================================================
    // Targets - Delivery Confirmation
    // =========================================================================

    /**
     * Confirm delivery of targets.
     *
     * @param int $submissionId Submission ID
     * @param list<int> $targetIds Target IDs to confirm
     * @return ConfirmTargetDeliveredResponse Response with successful/failed target IDs
     * @throws ApiException On API error
     */
    public function confirmTargetDelivery(int $submissionId, array $targetIds): ConfirmTargetDeliveredResponse
    {
        $this->ensureAuthenticated();
        $safeId = $this->validatePathSegment($submissionId);

        $result = $this->request(
            'POST',
            "/rest/v0/submissions/{$safeId}/targets/delivered",
            ['json' => ['targetIds' => $targetIds]]
        );

        return ConfirmTargetDeliveredResponse::fromJson($result['body']);
    }

    // =========================================================================
    // Private Methods
    // =========================================================================

    /**
     * Authenticate with the API and store the access token.
     */
    private function authenticate(): void
    {
        $loginRequest = new LoginRequest($this->username, $this->password);

        $headers = [
            'Authorization' => 'Basic ' . $this->basicAuth,
            'Content-Type' => self::CONTENT_TYPE_FORM,
            'User-Agent' => $this->userAgent,
        ];

        $body = http_build_query($loginRequest->toFormUrlEncoded());
        $response = $this->httpClient->send('POST', $this->baseUrl . '/oauth/token', $headers, $body);

        $this->handleErrorResponse($response);

        $loginResponse = LoginResponse::fromJson($response->body);

        if ($loginResponse->accessToken === null) {
            throw new AuthenticationException('Authentication failed: No access token received');
        }

        $this->accessToken = $loginResponse->accessToken;
        $this->tokenExpiresAt = $loginResponse->expiresIn !== null
            ? time() + $loginResponse->expiresIn
            : null;
    }

    /**
     * Ensure the client is authenticated, re-authenticating if the token is expired.
     */
    private function ensureAuthenticated(): void
    {
        if ($this->isTokenExpired()) {
            $this->authenticate();
        }
    }

    /**
     * Make an HTTP request to the API.
     *
     * @param string $method HTTP method
     * @param string $uri URI path
     * @param array<string, mixed> $options Request options (json, multipart, query, form_params)
     * @return array{body: string, status: int, headers: array<string, list<string>>}
     * @throws ApiException On API error
     */
    private function request(string $method, string $uri, array $options = []): array
    {
        $url = $this->baseUrl . $uri;

        // Support for query string
        if (isset($options['query']) && is_array($options['query'])) {
            $qs = http_build_query($options['query']);
            $url .= (str_contains($url, '?') ? '&' : '?') . $qs;
        }

        $headers = [
            'Accept' => self::CONTENT_TYPE_JSON,
            'Authorization' => 'Bearer ' . $this->accessToken,
            'User-Agent' => $this->userAgent,
        ];

        $body = null;

        // JSON body
        if (isset($options['json'])) {
            $headers['Content-Type'] = self::CONTENT_TYPE_JSON;
            $body = json_encode($options['json'], JSON_THROW_ON_ERROR);
        }

        // Multipart (file upload)
        if (isset($options['multipart'])) {
            $boundary = '----GlobalLinkPHPBoundary' . md5(uniqid());
            $headers['Content-Type'] = "multipart/form-data; boundary={$boundary}; charset=UTF-8";
            $body = $this->buildMultipartBody($options['multipart'], $boundary);
        }

        // x-www-form-urlencoded
        if (isset($options['form_params'])) {
            $headers['Content-Type'] = self::CONTENT_TYPE_FORM;
            $body = http_build_query($options['form_params']);
        }

        $attempt = 0;
            $maxAttempts = ($this->retryConfig !== null ? $this->retryConfig->maxRetries : 0) + 1;

        while (true) {
            $response = $this->httpClient->send($method, $url, $headers, $body);

            // Parse rate limit headers
            $this->parseRateLimitHeaders($response);

            $attempt++;

            // Success - return immediately
            if ($response->statusCode < 400) {
                return [
                    'body' => $response->body,
                    'status' => $response->statusCode,
                    'headers' => $response->headers,
                ];
            }

            // Check if we should retry
            $shouldRetry = false;
            if ($this->retryConfig !== null && $attempt < $maxAttempts) {
                if ($response->statusCode === 429 && $this->retryConfig->retryOnRateLimit) {
                    $shouldRetry = true;
                } elseif ($response->statusCode >= 500 && $this->retryConfig->retryOnServerError) {
                    $shouldRetry = true;
                }
            }

            if (!$shouldRetry) {
                $this->handleErrorResponse($response);
            }

            // Calculate delay: for 429 use Retry-After header, else exponential backoff
            $retryConfig = $this->retryConfig;
            assert($retryConfig !== null, 'RetryConfig required when retrying');
            $delayMs = $retryConfig->baseDelayMs * (2 ** ($attempt - 1));
            if ($response->statusCode === 429) {
                $retryAfter = $response->getHeader('Retry-After');
                if ($retryAfter !== null) {
                    $delayMs = (int) $retryAfter * 1000;
                }
            }

            usleep($delayMs * 1000);
        }
    }

    /**
     * Build a multipart/form-data body.
     *
     * @param list<array{name: string, contents: mixed, filename?: string}> $parts
     */
    private function buildMultipartBody(array $parts, string $boundary): string
    {
        $body = '';
        $resourcesToClose = [];

        try {
            foreach ($parts as $part) {
                $body .= "--{$boundary}\r\n";
                $disposition = "Content-Disposition: form-data; name=\"{$part['name']}\"";

                if (isset($part['filename'])) {
                    $safeFilename = $this->sanitizeFilename($part['filename']);
                    $disposition .= "; filename=\"" . addslashes($safeFilename) . "\"";
                }

                $body .= $disposition . "\r\n";

                if (isset($part['filename'])) {
                    $ext = strtolower(pathinfo($part['filename'], PATHINFO_EXTENSION));
                    $mime = $this->mimeTypeFromExtension($ext);
                    $body .= "Content-Type: {$mime}\r\n";
                }

                $body .= "\r\n";

                if (is_resource($part['contents'])) {
                    $resourcesToClose[] = $part['contents'];
                    $body .= stream_get_contents($part['contents']);
                } else {
                    $body .= (string) $part['contents'];
                }

                $body .= "\r\n";
            }

            $body .= "--{$boundary}--\r\n";

            return $body;
        } finally {
            // Close all file handles to prevent resource leaks
            foreach ($resourcesToClose as $resource) {
                if (is_resource($resource)) {
                    fclose($resource);
                }
            }
        }
    }

    /**
     * Get MIME type from file extension.
     */
    private function mimeTypeFromExtension(string $ext): string
    {
        static $map = [
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'json' => self::CONTENT_TYPE_JSON,
            'xml' => 'application/xml',
            'html' => 'text/html',
            'htm' => 'text/html',
            'xliff' => 'application/xliff+xml',
            'xlf' => 'application/xliff+xml',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip',
            'tar' => 'application/x-tar',
            'gz' => 'application/gzip',
        ];

        return $map[$ext] ?? 'application/octet-stream';
    }

    /**
     * Sanitize filename to prevent header injection attacks.
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove control characters (ASCII 0-31 and 127) to prevent CRLF injection
        $sanitized = preg_replace('/[\x00-\x1F\x7F]/', '', $filename) ?? $filename;
        // Also remove any path separators that could cause issues
        $sanitized = basename($sanitized);
        // Ensure we have a valid filename
        if (empty($sanitized)) {
            $sanitized = 'file';
        }
        return $sanitized;
    }

    /**
     * Validate a path segment to prevent path traversal attacks.
     *
     * @throws InvalidArgumentException If the value contains invalid characters
     */
    private function validatePathSegment(string|int $value): string
    {
        $stringValue = (string) $value;

        // Reject path traversal attempts and control characters
        if (str_contains($stringValue, '/') ||
            str_contains($stringValue, '\\') ||
            str_contains($stringValue, '..') ||
            preg_match('/[\x00-\x1F\x7F]/', $stringValue)) {
            throw new InvalidArgumentException(
                'Invalid path segment: contains forbidden characters'
            );
        }

        return $stringValue;
    }

    /**
     * Parse rate limit headers from response.
     */
    private function parseRateLimitHeaders(HttpResponse $response): void
    {
        $limit = $response->getHeader('X-RateLimit-Limit');
        $remaining = $response->getHeader('X-RateLimit-Remaining');
        $reset = $response->getHeader('X-RateLimit-Reset');

        if ($limit !== null || $remaining !== null || $reset !== null) {
            $this->lastRateLimitInfo = [
                'limit' => $limit !== null ? (int) $limit : null,
                'remaining' => $remaining !== null ? (int) $remaining : null,
                'reset' => $reset !== null ? (int) $reset : null,
            ];
        }
    }

    /**
     * Handle error responses from the API.
     *
     * @throws AuthenticationException On 401/403
     * @throws RateLimitException On 429
     * @throws ApiException On other 4xx/5xx
     */
    private function handleErrorResponse(HttpResponse $response): void
    {
        if ($response->statusCode < 400) {
            return;
        }

        // Extract error message from response body
        $message = "API error: {$response->statusCode}";
        $decodedBody = [];

        if (!empty($response->body) && json_validate($response->body)) {
            $decodedBody = json_decode($response->body, true);
            if (isset($decodedBody['message'])) {
                $message .= ': ' . $decodedBody['message'];
            } elseif (isset($decodedBody['error_description'])) {
                $message .= ': ' . $decodedBody['error_description'];
            } elseif (isset($decodedBody['error'])) {
                $message .= ': ' . $decodedBody['error'];
            }
        }

        // Handle specific status codes
        if ($response->statusCode === 401 || $response->statusCode === 403) {
            throw new AuthenticationException($message, $response->statusCode);
        }

        if ($response->statusCode === 429) {
            $retryAfter = $response->getHeader('Retry-After');
            throw new RateLimitException(
                $message,
                $retryAfter !== null ? (int) $retryAfter : null,
                $this->lastRateLimitInfo['limit'] ?? null,
                $this->lastRateLimitInfo['remaining'] ?? null,
                $this->lastRateLimitInfo['reset'] ?? null,
            );
        }

        // Don't include raw body in exception to avoid leaking secrets
        throw new ApiException($message, $response->statusCode);
    }
}
