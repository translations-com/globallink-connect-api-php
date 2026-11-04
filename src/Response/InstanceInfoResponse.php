<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Response containing instance information.
 */
readonly class InstanceInfoResponse
{
    public function __construct(
        public string $version = '',
        public int $maxUploadSize = 0,
        public int $maxDocumentsBeforeNewSubmission = 0,
        public int $restApiMaxClaimableItems = 0,
        public bool $tptMode = false,
    ) {
    }

    /**
     * Create an InstanceInfoResponse from a JSON string.
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
     * Create an InstanceInfoResponse from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            version: $data['version'] ?? '',
            maxUploadSize: $data['maxUploadSize'] ?? 0,
            maxDocumentsBeforeNewSubmission: $data['maxDocumentsBeforeNewSubmission'] ?? 0,
            restApiMaxClaimableItems: $data['restApiMaxClaimableItems'] ?? 0,
            tptMode: $data['tptMode'] ?? false,
        );
    }
}
