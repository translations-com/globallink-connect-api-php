<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents a language direction for a project.
 */
readonly class ProjectLanguageDirection
{
    public function __construct(
        public string $sourceLanguage,
        public string $sourceLanguageDisplayName,
        public string $targetLanguage,
        public string $targetLanguageDisplayName,
        public bool $default = false,
    ) {
    }

    /**
     * Create a ProjectLanguageDirection from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sourceLanguage: $data['sourceLanguage'] ?? '',
            sourceLanguageDisplayName: $data['sourceLanguageDisplayName'] ?? '',
            targetLanguage: $data['targetLanguage'] ?? '',
            targetLanguageDisplayName: $data['targetLanguageDisplayName'] ?? '',
            default: $data['default'] ?? false,
        );
    }
}
