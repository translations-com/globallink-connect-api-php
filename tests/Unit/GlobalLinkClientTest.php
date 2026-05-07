<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TransPerfect\GlobalLink\Exception\ApiException;
use TransPerfect\GlobalLink\Exception\AuthenticationException;
use TransPerfect\GlobalLink\Exception\InvalidArgumentException;
use TransPerfect\GlobalLink\Exception\RateLimitException;
use TransPerfect\GlobalLink\GlobalLinkClient;
use TransPerfect\GlobalLink\Http\HttpResponse;
use TransPerfect\GlobalLink\Request\ListProjectsRequest;
use TransPerfect\GlobalLink\Tests\Mock\MockHttpClient;

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

    public function testAuthenticationFailureThrowsException(): void
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
            $this->httpClient
        );
    }

    public function testGetProjectsReturnsProjects(): void
    {
        $client = $this->createClient();

        $projectsJson = $this->loadFixture('projects_list.json');
        $this->httpClient->enqueue(
            new HttpResponse(200, $projectsJson)
        );

        $projects = $client->getProjects();

        $this->assertCount(2, $projects);
        $this->assertSame('Test Project 1', $projects[0]->name);
        $this->assertSame(1001, $projects[0]->projectId);
        $this->assertSame('TP1', $projects[0]->shortCode);
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

        $project = $client->getProject(1001);

        $this->assertSame('Test Project', $project->name);
        $this->assertSame(1001, $project->projectId);
        $this->assertCount(1, $project->fileFormatProfiles);
        $this->assertCount(1, $project->workflowDefinitions);
        $this->assertCount(2, $project->projectLanguageDirections);
    }

    public function testRateLimitExceptionIsThrownOn429(): void
    {
        $client = $this->createClient();

        $headers = [
            'Retry-After' => ['60'],
            'X-RateLimit-Limit' => ['100'],
            'X-RateLimit-Remaining' => ['0'],
            'X-RateLimit-Reset' => ['1704110400'],
        ];

        $this->httpClient->enqueue(
            new HttpResponse(429, '{"message": "Rate limit exceeded"}', $headers)
        );

        try {
            $client->getProjects();
            $this->fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            $this->assertSame(60, $e->getRetryAfter());
            $this->assertSame(100, $e->getRateLimitLimit());
            $this->assertSame(0, $e->getRateLimitRemaining());
        }
    }

    public function testApiExceptionIsThrownOn500(): void
    {
        $client = $this->createClient();

        $this->httpClient->enqueue(
            new HttpResponse(500, '{"message": "Internal server error"}')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Internal server error');

        $client->getProjects();
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
