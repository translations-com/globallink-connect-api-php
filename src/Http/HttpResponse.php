<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Http;

/**
 * Value object representing an HTTP response.
 */
readonly class HttpResponse
{
    /**
     * @param int $statusCode HTTP status code
     * @param string $body Response body
     * @param array<string, list<string>> $headers Response headers (header name => list of values)
     */
    public function __construct(
        public int $statusCode,
        public string $body,
        public array $headers = [],
    ) {
    }

    /**
     * Get the first value of a header, or null if not present.
     */
    public function getHeader(string $name): ?string
    {
        $normalizedName = strtolower($name);
        foreach ($this->headers as $headerName => $values) {
            if (strtolower($headerName) === $normalizedName) {
                return $values[0] ?? null;
            }
        }
        return null;
    }

    /**
     * Get all values of a header.
     *
     * @return list<string>
     */
    public function getHeaderAll(string $name): array
    {
        $normalizedName = strtolower($name);
        foreach ($this->headers as $headerName => $values) {
            if (strtolower($headerName) === $normalizedName) {
                return $values;
            }
        }
        return [];
    }

    /**
     * Check if the response indicates success (2xx status).
     */
    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Check if the response indicates a client error (4xx status).
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Check if the response indicates a server error (5xx status).
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }
}
