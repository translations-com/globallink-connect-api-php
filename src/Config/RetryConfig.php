<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Config;

/**
 * Configuration for retry behavior on transient API failures.
 */
final readonly class RetryConfig
{
    public function __construct(
        public int $maxRetries = 3,
        public int $baseDelayMs = 1000,
        public bool $retryOnRateLimit = true,
        public bool $retryOnServerError = true,
    ) {
        if ($maxRetries < 0) {
            throw new \InvalidArgumentException('maxRetries must be non-negative');
        }
        if ($baseDelayMs < 0) {
            throw new \InvalidArgumentException('baseDelayMs must be non-negative');
        }
    }

    /**
     * Create a disabled retry config (no retries).
     */
    public static function disabled(): self
    {
        return new self(maxRetries: 0);
    }
}
