<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents a file format profile in GlobalLink.
 */
readonly class FileFormatProfile
{
    /**
     * @param list<string> $mimeTypes
     */
    public function __construct(
        public string $name,
        public int $fileFormatId,
        public bool $default = false,
        public bool $enabled = false,
        public array $mimeTypes = [],
        public bool $nonParsable = false,
        public string $targetFormat = '',
        public int $dateUpdated = 0,
        public int $defaultWorkflowDefinitionId = 0,
        public string $defaultWorkflowDefinitionName = '',
    ) {
    }

    /**
     * Create a FileFormatProfile from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            fileFormatId: $data['fileFormatId'] ?? 0,
            default: $data['default'] ?? false,
            enabled: $data['enabled'] ?? false,
            mimeTypes: $data['mimeTypes'] ?? [],
            nonParsable: $data['nonParsable'] ?? false,
            targetFormat: $data['targetFormat'] ?? '',
            dateUpdated: $data['dateUpdated'] ?? 0,
            defaultWorkflowDefinitionId: $data['defaultWorkflowDefinitionId'] ?? 0,
            defaultWorkflowDefinitionName: $data['defaultWorkflowDefinitionName'] ?? '',
        );
    }
}
