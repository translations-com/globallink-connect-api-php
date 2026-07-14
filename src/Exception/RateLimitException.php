<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Exception;

/**
 * Exception thrown when rate limit is exceeded (429).
 */
class RateLimitException extends ApiException
{
    public function __construct(
        string $message = 'Rate limit exceeded',
        private readonly ?int $retryAfter = null,
        private readonly ?int $rateLimitLimit = null,
        private readonly ?int $rateLimitRemaining = null,
        private readonly ?int $rateLimitReset = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 429, '', $previous);
    }

    /**
     * Get the number of seconds to wait before retrying.
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    /**
     * Get the rate limit maximum requests allowed.
     */
    public function getRateLimitLimit(): ?int
    {
        return $this->rateLimitLimit;
    }

    /**
     * Get the number of requests remaining in the current window.
     */
    public function getRateLimitRemaining(): ?int
    {
        return $this->rateLimitRemaining;
    }

    /**
     * Get the timestamp when the rate limit resets.
     */
    public function getRateLimitReset(): ?int
    {
        return $this->rateLimitReset;
    }
}
