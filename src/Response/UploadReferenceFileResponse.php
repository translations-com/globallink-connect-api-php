<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Response from uploading a reference file.
 */
readonly class UploadReferenceFileResponse
{
    public function __construct(
        public ?string $processId = null,
        public ?int $submissionId = null,
    ) {
    }

    /**
     * Create an UploadReferenceFileResponse from a JSON string.
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
     * Create an UploadReferenceFileResponse from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            processId: $data['processId'] ?? null,
            submissionId: $data['submissionId'] ?? null,
        );
    }
}
