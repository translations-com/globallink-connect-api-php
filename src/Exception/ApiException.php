<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Exception;

/**
 * Exception thrown when the API returns an error response (4xx/5xx).
 */
class ApiException extends GlobalLinkException
{
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly string $responseBody = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Get the HTTP status code from the failed request.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the response body from the failed request.
     * Note: May be empty or sanitized to avoid leaking sensitive data.
     */
    public function getResponseBody(): string
    {
        return $this->responseBody;
    }
}
