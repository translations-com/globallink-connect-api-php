<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Target ID information from upload source file response.
 */
readonly class UploadSourceFileTargetId
{
    public function __construct(
        public string $targetName = '',
        public int $submissionId = 0,
        public string $targetLanguage = '',
        public int $targetId = 0,
    ) {
    }

    /**
     * Create from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            targetName: $data['targetName'] ?? '',
            submissionId: $data['submissionId'] ?? 0,
            targetLanguage: $data['targetLanguage'] ?? '',
            targetId: $data['targetId'] ?? 0,
        );
    }
}

/**
 * Document ID information from upload source file response.
 */
readonly class UploadSourceFileDocumentId
{
    /**
     * @param list<UploadSourceFileTargetId> $targetIds
     */
    public function __construct(
        public string $name = '',
        public int $documentId = 0,
        public array $targetIds = [],
    ) {
    }

    /**
     * Create from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $targetIds = [];
        if (!empty($data['targetIds']) && is_array($data['targetIds'])) {
            foreach ($data['targetIds'] as $t) {
                $targetIds[] = UploadSourceFileTargetId::fromArray($t);
            }
        }

        return new self(
            name: $data['name'] ?? '',
            documentId: $data['documentId'] ?? 0,
            targetIds: $targetIds,
        );
    }
}

/**
 * Response from uploading a source file.
 */
readonly class UploadSourceFileResponse extends ApiResponse
{
    /**
     * @param list<UploadSourceFileDocumentId> $documentIds
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public string $processId = '',
        public int $submissionId = 0,
        public array $documentIds = [],
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
        $documentIds = [];
        if (!empty($data['documentIds']) && is_array($data['documentIds'])) {
            foreach ($data['documentIds'] as $d) {
                $documentIds[] = UploadSourceFileDocumentId::fromArray($d);
            }
        }

        return new self(
            processId: $data['processId'] ?? '',
            submissionId: $data['submissionId'] ?? 0,
            documentIds: $documentIds,
            responseCode: $responseCode,
            headers: $headers,
            responseBody: $responseBody,
        );
    }
}
