<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

use GlobalLink\RestClient\Model\SubmissionInfo;

/**
 * Response from getting or updating a submission.
 */
readonly class GetSubmissionResponse extends ApiResponse
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public ?SubmissionInfo $submission = null,
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

        $unusable = self::unusableSuccessBodyMessage($json, allowEmpty: true);
        if ($unusable !== null) {
            return new self(
                responseCode: $responseCode,
                errorMessage: $unusable,
                headers: $headers,
                responseBody: $json,
            );
        }

        // Empty 2xx (e.g. some PUT responses) — success with no submission payload
        if (trim($json) === '') {
            return new self(
                responseCode: $responseCode,
                headers: $headers,
                responseBody: $json,
            );
        }

        return new self(
            submission: SubmissionInfo::fromJson($json),
            responseCode: $responseCode,
            headers: $headers,
            responseBody: $json,
        );
    }
}
