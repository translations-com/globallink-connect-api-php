<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents a reference file attached to a submission.
 */
readonly class SubmissionReferenceFileItem
{
    public function __construct(
        public string $fileName,
        public string $repositoryTicket,
        public ?string $targetLanguageLocale = null,
        public ?string $targetLanguageValue = null,
    ) {
    }

    /**
     * Create a SubmissionReferenceFileItem from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fileName: $data['fileName'] ?? '',
            repositoryTicket: $data['repositoryTicket'] ?? '',
            targetLanguageLocale: $data['targetLanguageLocale'] ?? null,
            targetLanguageValue: $data['targetLanguageValue'] ?? null,
        );
    }
}
