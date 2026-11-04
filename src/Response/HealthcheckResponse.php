<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Response for GET /PD/health.pd.
 */
readonly class HealthcheckResponse extends ApiResponse
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public bool $healthy = false,
        public string $message = '',
        int $responseCode = 200,
        ?string $errorMessage = null,
        array $headers = [],
        string $responseBody = '',
    ) {
        parent::__construct($responseCode, $errorMessage, $headers, $responseBody);
    }

    /**
     * Parse the healthcheck response.
     *
     * @param string $body Response body (typically "OK")
     * @param int $statusCode HTTP status code
     * @param array<string, list<string>> $headers
     */
    public static function fromResponse(
        string $body,
        int $statusCode,
        array $headers = [],
    ): self {
        $healthy = $statusCode === 200;
        $message = trim($body);

        return new self(
            healthy: $healthy,
            message: $message,
            responseCode: $statusCode,
            errorMessage: self::extractErrorMessage($body, $statusCode),
            headers: $headers,
            responseBody: $body,
        );
    }
}
