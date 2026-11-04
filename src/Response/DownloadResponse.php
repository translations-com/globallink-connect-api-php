<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Response from downloading deliverable content.
 */
readonly class DownloadResponse extends ApiResponse
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public string $content = '',
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
                responseCode: $responseCode,
                errorMessage: $errorMessage,
                headers: $headers,
                responseBody: $body,
            );
        }

        return new self(
            content: $body,
            responseCode: $responseCode,
            headers: $headers,
            responseBody: $body,
        );
    }
}
