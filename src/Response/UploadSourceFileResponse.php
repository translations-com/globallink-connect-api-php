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
readonly class UploadSourceFileResponse
{
    /**
     * @param list<UploadSourceFileDocumentId> $documentIds
     */
    public function __construct(
        public string $processId = '',
        public int $submissionId = 0,
        public array $documentIds = [],
    ) {
    }

    /**
     * Create an UploadSourceFileResponse from a JSON string.
     */
    public static function fromJson(string $json): self
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        $data = json_decode($json, true);
        return self::fromArray($data);
    }

    /**
     * Create an UploadSourceFileResponse from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
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
        );
    }
}
