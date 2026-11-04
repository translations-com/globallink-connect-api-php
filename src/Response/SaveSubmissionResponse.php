<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Response from saving a submission.
 */
readonly class SaveSubmissionResponse extends ApiResponse
{
    /**
     * @param list<string> $messages
     * @param list<int> $startedSubmissionIds
     * @param list<int> $linkedSubmissionIds
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public array $messages = [],
        public array $startedSubmissionIds = [],
        public array $linkedSubmissionIds = [],
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
            messages: $data['messages'] ?? [],
            startedSubmissionIds: $data['startedSubmissionIds'] ?? [],
            linkedSubmissionIds: $data['linkedSubmissionIds'] ?? [],
            responseCode: $responseCode,
            headers: $headers,
            responseBody: $responseBody,
        );
    }
}
