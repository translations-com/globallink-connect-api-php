<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Response containing instance information.
 */
readonly class InstanceInfoResponse extends ApiResponse
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public string $version = '',
        public int $maxUploadSize = 0,
        public int $maxDocumentsBeforeNewSubmission = 0,
        public int $restApiMaxClaimableItems = 0,
        public bool $tptMode = false,
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
            version: $data['version'] ?? '',
            maxUploadSize: $data['maxUploadSize'] ?? 0,
            maxDocumentsBeforeNewSubmission: $data['maxDocumentsBeforeNewSubmission'] ?? 0,
            restApiMaxClaimableItems: $data['restApiMaxClaimableItems'] ?? 0,
            tptMode: $data['tptMode'] ?? false,
            responseCode: $responseCode,
            headers: $headers,
            responseBody: $responseBody,
        );
    }
}
