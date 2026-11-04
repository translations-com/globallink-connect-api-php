<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Response for GET /PD/health.pd.
 */
readonly class HealthcheckResponse
{
    public function __construct(
        public bool $healthy,
        public string $message = '',
    ) {
    }

    /**
     * Parse the healthcheck response.
     *
     * @param string $body Response body (typically "OK")
     * @param int $statusCode HTTP status code
     */
    public static function fromResponse(string $body, int $statusCode): self
    {
        $healthy = $statusCode === 200;
        $message = trim($body);

        return new self(
            healthy: $healthy,
            message: $message,
        );
    }
}
