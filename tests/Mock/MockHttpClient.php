<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Tests\Mock;

use GlobalLink\RestClient\Exception\GlobalLinkException;
use GlobalLink\RestClient\Http\HttpClientInterface;
use GlobalLink\RestClient\Http\HttpResponse;

/**
 * Mock HTTP client for testing.
 *
 * Allows tests to queue responses and inspect request history.
 */
class MockHttpClient implements HttpClientInterface
{
    /** @var list<HttpResponse> */
    private array $queue = [];

    /** @var list<array{method: string, url: string, headers: array<string, string>, body: ?string}> */
    private array $history = [];

    /**
     * Queue responses to be returned in order.
     */
    public function enqueue(HttpResponse ...$responses): void
    {
        foreach ($responses as $response) {
            $this->queue[] = $response;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function send(string $method, string $url, array $headers, ?string $body = null): HttpResponse
    {
        $this->history[] = [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
        ];

        if (empty($this->queue)) {
            throw new GlobalLinkException('MockHttpClient: No responses queued');
        }

        return array_shift($this->queue);
    }

    /**
     * Get the full request history.
     *
     * @return list<array{method: string, url: string, headers: array<string, string>, body: ?string}>
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * Get the last request made.
     *
     * @return array{method: string, url: string, headers: array<string, string>, body: ?string}
     */
    public function getLastRequest(): array
    {
        if (empty($this->history)) {
            throw new \RuntimeException('No requests have been made');
        }

        return $this->history[count($this->history) - 1];
    }

    /**
     * Get a specific request by index.
     *
     * @return array{method: string, url: string, headers: array<string, string>, body: ?string}
     */
    public function getRequest(int $index): array
    {
        if (!isset($this->history[$index])) {
            throw new \RuntimeException("No request at index {$index}");
        }

        return $this->history[$index];
    }

    /**
     * Assert that a specific number of requests were made.
     */
    public function assertRequestCount(int $expected): void
    {
        $actual = count($this->history);
        if ($actual !== $expected) {
            throw new \RuntimeException(
                "Expected {$expected} requests, but {$actual} were made"
            );
        }
    }

    /**
     * Clear the history and response queue.
     */
    public function reset(): void
    {
        $this->queue = [];
        $this->history = [];
    }
}
