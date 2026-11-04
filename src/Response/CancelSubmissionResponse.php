<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Response from cancelling a submission.
 */
readonly class CancelSubmissionResponse extends ApiResponse
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public bool $cancelled = false,
        int $responseCode = 200,
        ?string $errorMessage = null,
        array $headers = [],
        string $responseBody = '',
    ) {
        parent::__construct($responseCode, $errorMessage, $headers, $responseBody);
    }

    /**
     * @param array<string, list<string>> $headers
     */
    public static function fromHttpResult(
        string $body,
        int $responseCode = 200,
        array $headers = [],
    ): self {
        $errorMessage = self::extractErrorMessage($body, $responseCode);
        if ($errorMessage !== null) {
            return new self(
                cancelled: false,
                responseCode: $responseCode,
                errorMessage: $errorMessage,
                headers: $headers,
                responseBody: $body,
            );
        }

        return new self(
            cancelled: self::isHttpSuccessStatus($responseCode),
            responseCode: $responseCode,
            headers: $headers,
            responseBody: $body,
        );
    }
}
