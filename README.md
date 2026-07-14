# GlobalLink Connect PHP SDK

**Version:** 2.0.0

A PHP SDK for the GlobalLink Enterprise REST API, enabling seamless integration with TransPerfect's GlobalLink translation management platform.

## Features

- **Complete API Coverage**: All REST API endpoints for projects, submissions, targets, and downloads
- **OAuth2 Authentication**: Automatic token management and refresh
- **Type Safety**: Full PHP 8.3+ type hints and strict typing
- **Error Handling**: Typed exception hierarchy for precise error handling
- **Rate Limiting**: Built-in rate limit tracking and handling
- **Pagination**: Support for paginated API responses (pageSize: 20-200)

## Available Endpoints

| Category | Endpoints |
|----------|-----------|
| **Health & Info** | `healthcheck()`, `getInstanceInfo()` |
| **Projects** | `getProjects()`, `getProject()`, `getProjectFileFormats()`, `getProjectWorkflows()`, `getProjectCustomAttributes()`, `getProjectLanguageDirections()`, `getProjectOrgUsers()`, `getProjectMslaLevels()` |
| **Submissions** | `createSubmission()`, `getSubmissions()`, `getSubmission()`, `getSubmissionWordcount()`, `patchSubmission()`, `saveSubmission()`, `cancelSubmission()` |
| **File Upload** | `uploadSubmissionSourceFile()`, `uploadSubmissionReferenceFile()` |
| **Tech Tracking** | `putSubmissionTechTracking()` |
| **Targets** | `getTargets()` |
| **Downloads** | `downloadTargetDeliverable()`, `downloadSubmissionDeliverables()`, `downloadSubmissionOnlyDeliverables()`, `downloadSubmissionDeliverablesByLanguages()`, `downloadSubmissionDeliverablesByTargetIds()` |
| **Delivery** | `confirmTargetDelivery()` |

## Requirements

- **PHP 8.3** or higher
- **Composer** (for dependency management)

No additional PHP extensions are required. The SDK uses PHP's built-in stream functions for HTTP requests.

## Installation

Clone the repository and install dependencies:

```bash
git clone https://github.com/transperfect/globallink-connect-php.git
cd globallink-connect-php
composer install
```

To use as a dependency in another project, add to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/transperfect/globallink-connect-php"
        }
    ],
    "require": {
        "transperfect/globallink-connect-php": "*"
    }
}
```

Then run `composer update transperfect/globallink-connect-php`.

## Quick Start

### Configuration

Set your GlobalLink credentials as environment variables before running any script:

```bash
export GLE_API_URL="https://your-instance.globallink.com"
export GLE_USERNAME="your-username"
export GLE_PASSWORD="your-password"
export GLE_OAUTH_CLIENT="your-oauth-client-id"
export GLE_OAUTH_SECRET="your-oauth-client-secret"
```

To use a `.env` file, load it before creating the client. For example, with [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv):

```bash
composer require vlucas/phpdotenv
```

```php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
```

Or copy `.env.example` to `.env` and load variables manually.

### Basic Usage

```php
<?php

require_once 'vendor/autoload.php';

use GlobalLink\RestClient\GlobalLinkClient;
use GlobalLink\RestClient\Request\CreateSubmissionRequest;
use GlobalLink\RestClient\Request\UploadSourceFileRequest;
use GlobalLink\RestClient\Request\SaveSubmissionRequest;
use GlobalLink\RestClient\Request\GetTargetsRequest;
use GlobalLink\RestClient\Model\BatchInfo;
use GlobalLink\RestClient\Model\CreateSubmissionTargetLanguageInfo;

// Initialize the client
$client = new GlobalLinkClient(
    getenv('GLE_API_URL'),
    getenv('GLE_USERNAME'),
    getenv('GLE_PASSWORD'),
    getenv('GLE_OAUTH_CLIENT'),
    getenv('GLE_OAUTH_SECRET')
);

// List projects
$projects = $client->getProjects();
foreach ($projects as $project) {
    echo "Project: {$project->name} ({$project->shortCode})\n";
}

// Get project details
$projectInfo = $client->getProject($projects[0]->projectId);

// Create a submission
$batch = new BatchInfo(
    name: 'Batch 1',
    targetFormat: 'TXLF',
    targetLanguageInfos: [
        new CreateSubmissionTargetLanguageInfo('de-DE'),
        new CreateSubmissionTargetLanguageInfo('fr-FR'),
    ]
);

$request = new CreateSubmissionRequest(
    name: 'My Translation Job',
    dueDate: strtotime('+7 days') * 1000, // Unix milliseconds
    projectId: $projectInfo->projectId,
    sourceLanguage: 'en-US',
    batchInfos: [$batch],
    claimScope: 'LANGUAGE'
);

$createResponse = $client->createSubmission($request);
$submissionId = $createResponse->submissionId;
echo "Created submission: {$submissionId}\n";

// Upload a source file
$uploadRequest = new UploadSourceFileRequest(
    batchName: 'Batch 1',
    file: '/path/to/source-file.xml',
    fileFormatName: 'Default XML'
);

$client->uploadSubmissionSourceFile($submissionId, $uploadRequest);

// Save and start the submission
$saveRequest = new SaveSubmissionRequest(autoStart: true);
$client->saveSubmission($submissionId, $saveRequest);

// Poll for processed targets
$targetsRequest = new GetTargetsRequest('PROCESSED', submissionIds: [$submissionId]);
$targets = $client->getTargets($targetsRequest);

foreach ($targets as $target) {
    echo "Target {$target->targetId} ({$target->targetLanguage}): {$target->status}\n";
    
    // Download the translated file
    $content = $client->downloadTargetDeliverable($submissionId, $target->targetId);
    file_put_contents("translated_{$target->targetLanguage}.xml", $content);
    
    // Confirm delivery
    $client->confirmTargetDelivery($submissionId, [$target->targetId]);
}
```

## API Reference

### Authentication

The client automatically handles OAuth2 authentication and token refresh. Tokens are refreshed automatically when they expire.

```php
// Check if token is expired
if ($client->isTokenExpired()) {
    echo "Token needs refresh\n";
}
```

### Health Check

```php
// Check if the GlobalLink instance is healthy (no authentication required)
$health = $client->healthcheck();
if ($health->healthy) {
    echo "Instance is healthy: {$health->message}\n";
} else {
    echo "Instance is down!\n";
}
```

### Instance Information

```php
$info = $client->getInstanceInfo();
echo "Version: {$info->version}\n";
echo "Max upload size: {$info->maxUploadSize}\n";
echo "Max documents per submission: {$info->maxDocumentsBeforeNewSubmission}\n";
```

### Projects

```php
use GlobalLink\RestClient\Request\ListProjectsRequest;

// List all projects
$projects = $client->getProjects();

// List projects with filters
$request = new ListProjectsRequest(
    projectName: 'Marketing',
    pageSize: 50,
    pageNumber: 0
);
$projects = $client->getProjects($request);

// Get project details
$project = $client->getProject($projectId);

// Get project configuration
$fileFormats = $client->getProjectFileFormats($projectId);
$workflows = $client->getProjectWorkflows($projectId);
$customAttributes = $client->getProjectCustomAttributes($projectId);
$languageDirections = $client->getProjectLanguageDirections($projectId);
$users = $client->getProjectOrgUsers($projectId);

// Get MSLA (priority) levels
$mslaLevels = $client->getProjectMslaLevels($projectId);
foreach ($mslaLevels as $level) {
    echo "{$level->name} [{$level->priority}]\n";
}
```

### Submissions

```php
use GlobalLink\RestClient\Request\GetSubmissionsRequest;
use GlobalLink\RestClient\Request\UploadReferenceFileRequest;
use GlobalLink\RestClient\Model\TechTracking;
use GlobalLink\RestClient\Request\CancelSubmissionRequest;

// Create submission
$response = $client->createSubmission($request);

// Get submissions
$submissions = $client->getSubmissions();

// Get submissions with filters
$request = new GetSubmissionsRequest(
    statuses: ['IN_PROCESS', 'PROCESSED'],
    projectName: 'Marketing',
    pageSize: 50,
    pageNumber: 0
);
$submissions = $client->getSubmissions($request);

// Get single submission
$submission = $client->getSubmission($submissionId);

// Get submission wordcount (with leverage breakdown)
$wordcount = $client->getSubmissionWordcount($submissionId);
echo "Total words: {$wordcount->totalWordCount}\n";
echo "100% match: {$wordcount->match100}\n";
echo "No match: {$wordcount->noMatch}\n";

// Update submission (JSON Patch)
$submission = $client->patchSubmission($submissionId, 'replace', '/name', 'New Name');

// Upload source file
$uploadRequest = new UploadSourceFileRequest(
    batchName: 'Batch 1',
    fileContents: file_get_contents('/path/to/source.xml'),
    fileName: 'source.xml'
);
$client->uploadSubmissionSourceFile($submissionId, $uploadRequest);

// Upload reference file (glossaries, style guides, etc.)
// Option 1: From file path
$refRequest = new UploadReferenceFileRequest(
    file: '/path/to/glossary.txt',
    submissionLevel: true
);
$client->uploadSubmissionReferenceFile($submissionId, $refRequest);

// Option 2: From string contents
$refRequest = new UploadReferenceFileRequest(
    fileContents: file_get_contents('/path/to/glossary.txt'),
    fileName: 'glossary.txt',
    submissionLevel: true
);
$client->uploadSubmissionReferenceFile($submissionId, $refRequest);

// Set tech tracking (for analytics)
$techTracking = new TechTracking(
    adaptorName: 'GlobalLink Drupal Adaptor',
    adaptorVersion: '2.0.0',
    clientVersion: '11',
    technologyProduct: 'GLE'
);
$client->putSubmissionTechTracking($submissionId, $techTracking);

// Save submission (optionally auto-start)
$client->saveSubmission($submissionId, new SaveSubmissionRequest(autoStart: true));

// Cancel submission (cancel specific targets or documents)
$client->cancelSubmission($submissionId, new CancelSubmissionRequest(
    targetIds: [123, 456]  // or documentIds: [1, 2]
));
```

### Targets

```php
use GlobalLink\RestClient\Request\GetTargetsRequest;

// Get targets by status with pagination
// Note: pageSize must be between 20 and 200 (API requirement)
$request = new GetTargetsRequest(
    targetStatus: 'PROCESSED',
    submissionIds: [$submissionId],
    pageSize: 50,
    pageNumber: 0
);
$targets = $client->getTargets($request);

// Get cancelled targets
$cancelledRequest = new GetTargetsRequest(
    targetStatus: 'CANCELLED',
    projectIds: [$projectId]
);
$cancelledTargets = $client->getTargets($cancelledRequest);

// Download single target deliverable
$content = $client->downloadTargetDeliverable($submissionId, $targetId);
file_put_contents('translated.xml', $content);

// Download all deliverables (ZIP)
$zipContent = $client->downloadSubmissionDeliverables($submissionId, includeManifest: true);

// Download only deliverables (no source/reference files)
$zipContent = $client->downloadSubmissionOnlyDeliverables($submissionId);

// Download by target languages
$zipContent = $client->downloadSubmissionDeliverablesByLanguages($submissionId, ['de-DE', 'fr-FR']);

// Download specific targets by ID
$zipContent = $client->downloadSubmissionDeliverablesByTargetIds($submissionId, [$targetId1, $targetId2]);

// Confirm delivery (marks targets as delivered)
$response = $client->confirmTargetDelivery($submissionId, [$targetId1, $targetId2]);
echo "Delivered: " . count($response->successfullyDeliveredTargets) . "\n";
echo "Failed: " . count($response->failedToDeliverTargets) . "\n";
```

## Error Handling

The SDK provides a typed exception hierarchy:

```php
use GlobalLink\RestClient\Exception\GlobalLinkException;
use GlobalLink\RestClient\Exception\ApiException;
use GlobalLink\RestClient\Exception\AuthenticationException;
use GlobalLink\RestClient\Exception\RateLimitException;
use GlobalLink\RestClient\Exception\InvalidArgumentException;

try {
    $projects = $client->getProjects();
} catch (AuthenticationException $e) {
    // Handle authentication errors (401/403)
    echo "Authentication failed: " . $e->getMessage();
} catch (RateLimitException $e) {
    // Handle rate limiting (429)
    echo "Rate limited. Retry after: " . $e->getRetryAfter() . " seconds";
} catch (ApiException $e) {
    // Handle other API errors (4xx/5xx)
    echo "API error ({$e->getStatusCode()}): " . $e->getMessage();
} catch (GlobalLinkException $e) {
    // Handle all SDK errors
    echo "Error: " . $e->getMessage();
}
```

### Rate Limit Information

```php
// Access rate limit info after any request
$rateLimitInfo = $client->getRateLimitInfo();
if ($rateLimitInfo !== null) {
    echo "Requests remaining: {$rateLimitInfo['remaining']}/{$rateLimitInfo['limit']}\n";
}
```

## Advanced Usage

### Custom HTTP Client

You can inject a custom HTTP client for testing or to use a different HTTP library:

```php
use GlobalLink\RestClient\Http\HttpClientInterface;
use GlobalLink\RestClient\Http\HttpResponse;

class GuzzleHttpClient implements HttpClientInterface
{
    private \GuzzleHttp\Client $guzzle;

    public function __construct()
    {
        $this->guzzle = new \GuzzleHttp\Client();
    }

    public function send(string $method, string $url, array $headers, ?string $body = null): HttpResponse
    {
        $response = $this->guzzle->request($method, $url, [
            'headers' => $headers,
            'body' => $body,
            'http_errors' => false,
        ]);

        return new HttpResponse(
            $response->getStatusCode(),
            (string) $response->getBody(),
            $response->getHeaders()
        );
    }
}

$client = new GlobalLinkClient(
    // ... credentials ...
    httpClient: new GuzzleHttpClient()
);
```

### Custom User-Agent

```php
$client = new GlobalLinkClient(
    // ... credentials ...
    userAgent: 'MyApp/1.0 (Custom Integration)'
);
```

### Retry Configuration

Enable automatic retries for transient failures (429 rate limit, 5xx server errors):

```php
use GlobalLink\RestClient\Config\RetryConfig;

$client = new GlobalLinkClient(
    // ... credentials ...
    retryConfig: new RetryConfig(
        maxRetries: 3,
        baseDelayMs: 1000,
        retryOnRateLimit: true,
        retryOnServerError: true
    )
);
```

## Examples

The SDK includes comprehensive example scripts demonstrating all functionality:

| Example | Description |
|---------|-------------|
| [`examples/01-projects.php`](examples/01-projects.php) | Health check, instance info, projects, file formats, workflows, language directions, custom attributes, organization users, MSLA levels |
| [`examples/02-submissions.php`](examples/02-submissions.php) | Create submissions, upload source/reference files, tech tracking, save, wordcount, cancel |
| [`examples/03-targets.php`](examples/03-targets.php) | Poll targets, pagination, download deliverables, bulk downloads, confirm delivery |

Run examples from the project root after setting credentials:

```bash
# Set credentials (or load from .env)
export GLE_API_URL="https://your-instance.globallink.com"
export GLE_USERNAME="your-username"
export GLE_PASSWORD="your-password"
export GLE_OAUTH_CLIENT="your-oauth-client-id"
export GLE_OAUTH_SECRET="your-oauth-client-secret"

# Run an example
php examples/01-projects.php
php examples/02-submissions.php
php examples/03-targets.php
```

## Pagination

The GlobalLink API uses pagination for list endpoints. Important notes:

- `pageSize` must be between **20 and 200** (default: 50)
- `pageNumber` is 0-indexed (default: 0)
- The SDK automatically clamps `pageSize` values to the valid range (20-200)
- See [API pagination documentation](https://onlinehelp.transperfect.com/GLE/RESTAPI/5-using-pagination.html)

```php
// Paginate through all targets
$pageNumber = 0;
$allTargets = [];

do {
    $request = new GetTargetsRequest(
        targetStatus: 'PROCESSED',
        projectIds: [$projectId],
        pageSize: 50,
        pageNumber: $pageNumber
    );
    
    $targets = $client->getTargets($request);
    $allTargets = array_merge($allTargets, $targets);
    $pageNumber++;
} while (count($targets) === 50);
```

## Running Tests

```bash
# 1. Install dependencies
composer install

# 2. Run all tests (unit + integration)
composer test

# 3. Run only unit tests (no network or credentials required)
composer test:unit

# 4. Run integration tests (requires .env.test with valid credentials)
composer test:integration

# 5. Run with coverage report
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html coverage/
```

### Integration Tests Setup

1. Copy `.env.test.example` to `.env.test`:
   ```bash
   cp .env.test.example .env.test
   ```

2. Edit `.env.test` and fill in your GlobalLink sandbox credentials:

```bash
# Required
GLE_TEST_API_URL=https://sandbox.globallink.com
GLE_TEST_USERNAME=your-test-username
GLE_TEST_PASSWORD=your-test-password
GLE_TEST_OAUTH_CLIENT=your-oauth-client-id
GLE_TEST_OAUTH_SECRET=your-oauth-client-secret

# Optional (for submission workflow tests)
GLE_TEST_PROJECT_ID=1001
GLE_FILE_FORMAT_NAME=XML
GLE_SOURCE_LANGUAGE=en-US
GLE_TARGET_LANGUAGES=de-DE,ja-JP
GLE_TEST_FILE_NAME=test.xml
```

3. Run integration tests:
   ```bash
   composer test:integration
   ```

## Contributing

This repository is maintained exclusively by TransPerfect. External contributions such as pull requests are not accepted. If you encounter a bug or have a feature request, please contact your TransPerfect representative or reach out via [GlobalLink Support](https://www.transperfect.com/globallink/support).

## Security

If you discover a security vulnerability in this SDK, please report it responsibly. **Do not open a public GitHub issue for security vulnerabilities.**

Instead, please contact TransPerfect directly at [globallink@transperfect.com](mailto:globallink@transperfect.com) with:

- A description of the vulnerability
- Steps to reproduce the issue
- Any potential impact

We will acknowledge receipt within 3 business days and work to address verified vulnerabilities promptly.

## License

This software is proprietary and confidential. Unauthorized copying, distribution, or use is strictly prohibited.

Copyright (c) TransPerfect. All rights reserved.

## Support

For support, please contact your TransPerfect representative or visit [GlobalLink Support](https://www.transperfect.com/globallink/support).
