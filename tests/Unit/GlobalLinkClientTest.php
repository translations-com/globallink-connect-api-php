<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use GlobalLink\RestClient\Exception\AuthenticationException;
use GlobalLink\RestClient\Exception\InvalidArgumentException;
use GlobalLink\RestClient\Exception\RateLimitException;
use GlobalLink\RestClient\GlobalLinkClient;
use GlobalLink\RestClient\Http\HttpResponse;
use GlobalLink\RestClient\Request\GetTargetsRequest;
use GlobalLink\RestClient\Request\ListProjectsRequest;
use GlobalLink\RestClient\Tests\Mock\MockHttpClient;

/**
 * Unit tests for GlobalLinkClient.
 */
class GlobalLinkClientTest extends TestCase
{
    private MockHttpClient $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = new MockHttpClient();
    }

    private function getLoginResponse(): string
    {
        return $this->loadFixture('login_response.json');
    }

    private function loadFixture(string $filename): string
    {
        $path = __DIR__ . '/../Fixture/' . $filename;
        $content = file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException("Could not load fixture: {$filename}");
        }
        return $content;
    }

    private function createClient(): GlobalLinkClient
    {
        // Queue the login response
        $this->httpClient->enqueue(
            new HttpResponse(200, $this->getLoginResponse())
        );

        return new GlobalLinkClient(
            'https://api.example.com',
            'testuser',
            'testpass',
            'client_id',
            'client_secret',
            $this->httpClient
        );
    }

    public function testAuthenticationSendsCorrectRequest(): void
    {
        $this->createClient();

        $this->httpClient->assertRequestCount(1);
        $request = $this->httpClient->getRequest(0);

        $this->assertSame('POST', $request['method']);
        $this->assertSame('https://api.example.com/oauth/token', $request['url']);
        $this->assertArrayHasKey('Authorization', $request['headers']);
        $this->assertStringStartsWith('Basic ', $request['headers']['Authorization']);
        $this->assertStringContainsString('grant_type=password', $request['body']);
        $this->assertStringContainsString('username=testuser', $request['body']);
    }

    public function testAuthenticationFailureThrowsWhenRequested(): void
    {
        $this->httpClient->enqueue(
            new HttpResponse(401, '{"error": "invalid_credentials"}')
        );

        $this->expectException(AuthenticationException::class);

        new GlobalLinkClient(
            'https://api.example.com',
            'testuser',
            'wrongpass',
            'client_id',
            'client_secret',
            $this->httpClient,
            throwOnAuthenticationFailure: true,
        );
    }

    public function testRateLimitedLoginThrowsRateLimitExceptionWhenRequested(): void
    {
        $body = '{"message": "Rate limit exceeded"}';
        $this->httpClient->enqueue(
            new HttpResponse(429, $body, [
                'Retry-After' => ['30'],
                'X-RateLimit-Limit' => ['100'],
                'X-RateLimit-Remaining' => ['0'],
                'X-RateLimit-Reset' => ['1704110400'],
            ])
        );

        try {
            new GlobalLinkClient(
                'https://api.example.com',
                'testuser',
                'testpass',
                'client_id',
                'client_secret',
                $this->httpClient,
                throwOnAuthenticationFailure: true,
            );
            $this->fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            $this->assertSame('Rate limit exceeded', $e->getMessage());
            $this->assertSame(429, $e->getStatusCode());
            $this->assertSame($body, $e->getResponseBody());
            $this->assertSame(30, $e->getRetryAfter());
            $this->assertSame(100, $e->getRateLimitLimit());
            $this->assertSame(0, $e->getRateLimitRemaining());
            $this->assertSame(1704110400, $e->getRateLimitReset());
        }
    }

    public function testAuthenticationFailureDoesNotThrowByDefault(): void
    {
        $this->httpClient->enqueue(
            new HttpResponse(401, '{"error": "invalid_credentials"}')
        );

        $client = new GlobalLinkClient(
            'https://api.example.com',
            'testuser',
            'wrongpass',
            'client_id',
            'client_secret',
            $this->httpClient
        );

        $this->assertTrue($client->isTokenExpired());
    }

    public function testGetProjectsReturnsProjects(): void
    {
        $client = $this->createClient();

        $projectsJson = $this->loadFixture('projects_list.json');
        $this->httpClient->enqueue(
            new HttpResponse(200, $projectsJson)
        );

        $response = $client->getProjects();

        $this->assertTrue($response->isSuccess());
        $this->assertNull($response->errorMessage);
        $this->assertSame(200, $response->responseCode);
        $this->assertSame($projectsJson, $response->responseBody);
        $this->assertCount(2, $response->projects);
        $this->assertSame('Test Project 1', $response->projects[0]->name);
        $this->assertSame(1001, $response->projects[0]->projectId);
        $this->assertSame('TP1', $response->projects[0]->shortCode);
    }

    public function testGetProjectsWithFilters(): void
    {
        $client = $this->createClient();

        $this->httpClient->enqueue(
            new HttpResponse(200, '[]')
        );

        $request = new ListProjectsRequest(
            projectName: 'Test',
            pageSize: 50
        );

        $client->getProjects($request);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertStringContainsString('projectName=Test', $lastRequest['url']);
        $this->assertStringContainsString('pageSize=50', $lastRequest['url']);
    }

    public function testGetProjectReturnsProjectInfo(): void
    {
        $client = $this->createClient();

        $projectJson = $this->loadFixture('project_detail.json');
        $this->httpClient->enqueue(
            new HttpResponse(200, $projectJson)
        );

        $response = $client->getProject(1001);

        $this->assertTrue($response->isSuccess());
        $this->assertNotNull($response->project);
        $this->assertSame('Test Project', $response->project->name);
        $this->assertSame(1001, $response->project->projectId);
        $this->assertCount(1, $response->project->fileFormatProfiles);
        $this->assertCount(1, $response->project->workflowDefinitions);
        $this->assertCount(2, $response->project->projectLanguageDirections);
    }

    public function testRateLimitReturnsErrorResponseOn429(): void
    {
        $client = $this->createClient();

        $headers = [
            'Retry-After' => ['60'],
            'X-RateLimit-Limit' => ['100'],
            'X-RateLimit-Remaining' => ['0'],
            'X-RateLimit-Reset' => ['1704110400'],
        ];
        $body = '{"message": "Rate limit exceeded"}';

        $this->httpClient->enqueue(
            new HttpResponse(429, $body, $headers)
        );

        $response = $client->getProjects();

        $this->assertTrue($response->isError());
        $this->assertSame(429, $response->responseCode);
        $this->assertSame('Rate limit exceeded', $response->errorMessage);
        $this->assertSame([], $response->projects);
        $this->assertSame(['60'], $response->headers['Retry-After']);
        $this->assertSame(100, $client->getRateLimitInfo()['limit']);
        $this->assertSame(0, $client->getRateLimitInfo()['remaining']);
    }

    public function testServerErrorReturnsErrorResponseOn500(): void
    {
        $client = $this->createClient();
        $body = '{"message": "Internal server error"}';

        $this->httpClient->enqueue(
            new HttpResponse(500, $body)
        );

        $response = $client->getProjects();

        $this->assertTrue($response->isError());
        $this->assertSame(500, $response->responseCode);
        $this->assertSame('Internal server error', $response->errorMessage);
        $this->assertSame($body, $response->responseBody);
        $this->assertSame([], $response->projects);
    }

    public function testErrorResponseUsesStatusCodeWhenBodyHasNoMessage(): void
    {
        $client = $this->createClient();
        $body = '{"detail": "something went wrong"}';

        $this->httpClient->enqueue(
            new HttpResponse(404, $body)
        );

        $response = $client->getProjects();

        $this->assertTrue($response->isError());
        $this->assertSame(404, $response->responseCode);
        $this->assertSame('404', $response->errorMessage);
        $this->assertSame($body, $response->responseBody);
        $this->assertSame([], $response->projects);
    }

    public function testUnauthorizedApiCallReturnsErrorResponse(): void
    {
        $client = $this->createClient();
        $body = '{"error_description": "Invalid credentials"}';

        $this->httpClient->enqueue(
            new HttpResponse(401, $body)
        );

        $response = $client->getProjects();

        $this->assertTrue($response->isError());
        $this->assertSame(401, $response->responseCode);
        $this->assertSame('Invalid credentials', $response->errorMessage);
        $this->assertSame([], $response->projects);
    }

    public function testTransportFailureReturnsErrorResponse(): void
    {
        $client = $this->createClient();
        $message = 'Unable to connect to https://api.example.com/rest/v0/projects: Connection refused';

        $this->httpClient->enqueue(
            new HttpResponse(0, $message)
        );

        $response = $client->getProjects();

        $this->assertTrue($response->isError());
        $this->assertFalse($response->isSuccess());
        $this->assertSame(0, $response->responseCode);
        $this->assertSame($message, $response->errorMessage);
        $this->assertSame($message, $response->responseBody);
        $this->assertSame([], $response->projects);
    }

    public function testRedirectIsNotTreatedAsSuccess(): void
    {
        $client = $this->createClient();
        $this->httpClient->enqueue(
            new HttpResponse(302, '<html>redirect</html>', ['Location' => ['https://example.com']])
        );

        $response = $client->getProjects();

        $this->assertTrue($response->isError());
        $this->assertFalse($response->isSuccess());
        $this->assertSame(302, $response->responseCode);
        $this->assertSame([], $response->projects);
    }

    public function testEmpty204ListIsSuccessWithEmptyProjects(): void
    {
        $client = $this->createClient();
        $this->httpClient->enqueue(new HttpResponse(204, ''));

        $response = $client->getProjects();

        $this->assertTrue($response->isSuccess());
        $this->assertSame(204, $response->responseCode);
        $this->assertNull($response->errorMessage);
        $this->assertSame([], $response->projects);
    }

    public function testInvalidJson2xxIsErrorNotException(): void
    {
        $client = $this->createClient();
        $this->httpClient->enqueue(new HttpResponse(200, 'not-json'));

        $response = $client->getProjects();

        $this->assertTrue($response->isError());
        $this->assertFalse($response->isSuccess());
        $this->assertSame(200, $response->responseCode);
        $this->assertSame('Invalid JSON response', $response->errorMessage);
        $this->assertSame([], $response->projects);
    }


    public function testThrownTransportExceptionIsMappedToErrorResponse(): void
    {
        $client = $this->createClient();
        // Empty queue causes MockHttpClient to throw; client maps that to status 0.
        $response = $client->getProjects();

        $this->assertTrue($response->isError());
        $this->assertSame(0, $response->responseCode);
        $this->assertStringContainsString('No responses queued', (string) $response->errorMessage);
        $this->assertSame([], $response->projects);
    }

    public function testReAuthFailureReturnsErrorResponse(): void
    {
        $this->httpClient->enqueue(
            new HttpResponse(200, json_encode([
                'access_token' => 'initial_token',
                'token_type' => 'bearer',
                'expires_in' => 1,
            ]))
        );

        $client = new GlobalLinkClient(
            'https://api.example.com',
            'testuser',
            'testpass',
            'client_id',
            'client_secret',
            $this->httpClient
        );

        $this->httpClient->enqueue(
            new HttpResponse(401, '{"error_description": "token refresh failed"}')
        );

        $response = $client->getProjects();

        $this->assertTrue($response->isError());
        $this->assertSame(401, $response->responseCode);
        $this->assertSame('token refresh failed', $response->errorMessage);
        $this->assertSame([], $response->projects);
    }

    public function testArrayErrorMessageIsJoined(): void
    {
        $client = $this->createClient();
        $body = '{"message":["name is required","due date is required"]}';

        $this->httpClient->enqueue(new HttpResponse(400, $body));

        $response = $client->getProjects();

        $this->assertTrue($response->isError());
        $this->assertSame('name is required; due date is required', $response->errorMessage);
        $this->assertSame($body, $response->responseBody);
    }

    public function testGlobalLinkMessagesListIsUsedAsErrorMessage(): void
    {
        $client = $this->createClient();

        $this->httpClient->enqueue(
            new HttpResponse(500, '{"messages":["Unexpected error during execution of API method!"]}')
        );

        $response = $client->getProjects();

        $this->assertTrue($response->isError());
        $this->assertSame('Unexpected error during execution of API method!', $response->errorMessage);
    }

    public function testObjectErrorFieldIsJsonEncoded(): void
    {
        $client = $this->createClient();

        $this->httpClient->enqueue(
            new HttpResponse(400, '{"error":{"code":"invalid_request","detail":"rejected"}}')
        );

        $response = $client->getProjects();

        $this->assertSame('{"code":"invalid_request","detail":"rejected"}', $response->errorMessage);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function wrongShapeListBodies(): array
    {
        return [
            'bare number' => ['5'],
            'bare string' => ['"abc"'],
            'list of numbers' => ['[1,2]'],
            'wrapped list of numbers' => ['{"projects":[1]}'],
        ];
    }

    #[DataProvider('wrongShapeListBodies')]
    public function testWrongShapeListBodyIsErrorNotException(string $body): void
    {
        $client = $this->createClient();
        $this->httpClient->enqueue(new HttpResponse(200, $body));

        $response = $client->getProjects();

        $this->assertTrue($response->isError());
        $this->assertSame(200, $response->responseCode);
        $this->assertSame('Unexpected JSON response', $response->errorMessage);
        $this->assertSame([], $response->projects);
    }

    public function testWrongShapeSingleResourceBodyIsErrorNotException(): void
    {
        $client = $this->createClient();
        $this->httpClient->enqueue(new HttpResponse(200, '"not an object"'));

        $response = $client->getProject(1001);

        $this->assertTrue($response->isError());
        $this->assertSame('Unexpected JSON response', $response->errorMessage);
        $this->assertNull($response->project);
    }

    public function testWrongShapeTargetsBodyIsErrorNotException(): void
    {
        $client = $this->createClient();
        $this->httpClient->enqueue(new HttpResponse(200, '[1]'));

        $response = $client->getTargets(new GetTargetsRequest('PROCESSED'));

        $this->assertTrue($response->isError());
        $this->assertSame('Unexpected JSON response', $response->errorMessage);
        $this->assertSame([], $response->targets);
    }

    public function testUserAgentHeaderIsSent(): void
    {
        $client = $this->createClient();

        $this->httpClient->enqueue(
            new HttpResponse(200, '[]')
        );

        $client->getProjects();

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertArrayHasKey('User-Agent', $lastRequest['headers']);
        $this->assertStringContainsString('globallink-connect-php', $lastRequest['headers']['User-Agent']);
    }

    public function testCustomUserAgentIsUsed(): void
    {
        $this->httpClient->enqueue(
            new HttpResponse(200, $this->getLoginResponse())
        );

        $client = new GlobalLinkClient(
            'https://api.example.com',
            'testuser',
            'testpass',
            'client_id',
            'client_secret',
            $this->httpClient,
            'CustomApp/1.0'
        );

        $this->httpClient->enqueue(
            new HttpResponse(200, '[]')
        );

        $client->getProjects();

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertSame('CustomApp/1.0', $lastRequest['headers']['User-Agent']);
    }

    public function testPathTraversalIsRejected(): void
    {
        $client = $this->createClient();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid path segment');

        $client->getProject('../1001');
    }

    public function testSlashInIdIsRejected(): void
    {
        $client = $this->createClient();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid path segment');

        $client->getProject('1001/attack');
    }

    public function testTokenRefreshOnExpiry(): void
    {
        // Initial login
        $this->httpClient->enqueue(
            new HttpResponse(200, json_encode([
                'access_token' => 'initial_token',
                'token_type' => 'bearer',
                'expires_in' => 1, // Expires immediately
            ]))
        );

        $client = new GlobalLinkClient(
            'https://api.example.com',
            'testuser',
            'testpass',
            'client_id',
            'client_secret',
            $this->httpClient
        );

        // Wait for token to expire (1 second + 60 second buffer means it's expired)
        // The token expiry check uses a 60-second buffer, so with expires_in=1, it's immediately expired

        // Queue refresh login response
        $this->httpClient->enqueue(
            new HttpResponse(200, json_encode([
                'access_token' => 'refreshed_token',
                'token_type' => 'bearer',
                'expires_in' => 3600,
            ]))
        );

        // Queue the actual API response
        $this->httpClient->enqueue(
            new HttpResponse(200, '[]')
        );

        $client->getProjects();

        // Should have 3 requests: initial login, refresh login, getProjects
        $this->assertCount(3, $this->httpClient->getHistory());

        // Verify the second request was a token refresh (login)
        $refreshRequest = $this->httpClient->getRequest(1);
        $this->assertSame('POST', $refreshRequest['method']);
        $this->assertStringContainsString('/oauth/token', $refreshRequest['url']);
    }

    public function testRateLimitInfoIsParsed(): void
    {
        $client = $this->createClient();

        $headers = [
            'X-RateLimit-Limit' => ['100'],
            'X-RateLimit-Remaining' => ['95'],
            'X-RateLimit-Reset' => ['1704110400'],
        ];

        $this->httpClient->enqueue(
            new HttpResponse(200, '[]', $headers)
        );

        $client->getProjects();

        $rateLimitInfo = $client->getRateLimitInfo();
        $this->assertNotNull($rateLimitInfo);
        $this->assertSame(100, $rateLimitInfo['limit']);
        $this->assertSame(95, $rateLimitInfo['remaining']);
        $this->assertSame(1704110400, $rateLimitInfo['reset']);
    }

}
