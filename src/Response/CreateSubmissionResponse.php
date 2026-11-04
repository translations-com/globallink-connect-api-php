<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Response from creating a submission.
 */
readonly class CreateSubmissionResponse extends ApiResponse
{
    /**
     * @param list<string> $messages
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public int $submissionId = 0,
        public array $messages = [],
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
    public static function fromJson(
        string $json,
        int $responseCode = 200,
        array $headers = [],
    ): self {
        $errorMessage = self::extractErrorMessage($json, $responseCode);
        if ($errorMessage !== null) {
            return new self(
                responseCode: $responseCode,
                errorMessage: $errorMessage,
                headers: $headers,
                responseBody: $json,
            );
        }

        $unusable = self::unusableSuccessBodyMessage($json, allowEmpty: false);
        if ($unusable !== null) {
            return new self(
                responseCode: $responseCode,
                errorMessage: $unusable,
                headers: $headers,
                responseBody: $json,
            );
        }

        $data = json_decode($json, true);
        return self::fromArray($data, $responseCode, $headers, $json);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $headers
     */
    public static function fromArray(
        array $data,
        int $responseCode = 200,
        array $headers = [],
        string $responseBody = '',
    ): self {
        return new self(
            submissionId: $data['submissionId'] ?? 0,
            messages: $data['messages'] ?? [],
            responseCode: $responseCode,
            headers: $headers,
            responseBody: $responseBody,
        );
    }
}
