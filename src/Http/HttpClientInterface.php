<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Http;

/**
 * Interface for HTTP clients.
 *
 * This interface allows the SDK to work with any HTTP client implementation,
 * making it possible to inject custom clients (e.g., Guzzle) or mock clients for testing.
 */
interface HttpClientInterface
{
    /**
     * Send an HTTP request.
     *
     * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @param string $url Full URL to send the request to
     * @param array<string, string> $headers Request headers
     * @param string|null $body Request body (for POST, PUT, PATCH)
     * @return HttpResponse The response from the server
     * @throws \GlobalLink\RestClient\Exception\GlobalLinkException On connection failure
     */
    public function send(string $method, string $url, array $headers, ?string $body = null): HttpResponse;
}
