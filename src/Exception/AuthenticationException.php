<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Exception;

/**
 * Exception thrown when authentication fails (401/403).
 */
class AuthenticationException extends ApiException
{
    public function __construct(
        string $message = 'Authentication failed',
        int $statusCode = 401,
        string $responseBody = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $responseBody, $previous);
    }
}
