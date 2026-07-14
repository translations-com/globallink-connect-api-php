<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents a translation target in GlobalLink.
 */
readonly class Target
{
    /**
     * @param list<array<string, mixed>> $translatableNames
     */
    public function __construct(
        public ?string $status = null,
        public ?int $targetId = null,
        public ?int $submissionId = null,
        public ?string $sourceLanguage = null,
        public ?string $targetLanguage = null,
        public ?int $jobId = null,
        public ?int $documentId = null,
        public ?string $fileFormatName = null,
        public ?int $dueDate = null,
        public ?string $documentName = null,
        public ?int $dateCompleted = null,
        public ?int $fileFormatId = null,
        public ?string $currentPhase = null,
        public array $translatableNames = [],
    ) {
    }

    /**
     * Create a Target from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? null,
            targetId: $data['targetId'] ?? null,
            submissionId: $data['submissionId'] ?? null,
            sourceLanguage: $data['sourceLanguage'] ?? null,
            targetLanguage: $data['targetLanguage'] ?? null,
            jobId: $data['jobId'] ?? null,
            documentId: $data['documentId'] ?? null,
            fileFormatName: $data['fileFormatName'] ?? null,
            dueDate: $data['dueDate'] ?? null,
            documentName: $data['documentName'] ?? null,
            dateCompleted: $data['dateCompleted'] ?? null,
            fileFormatId: $data['fileFormatId'] ?? null,
            currentPhase: $data['currentPhase'] ?? null,
            translatableNames: $data['translatableNames'] ?? [],
        );
    }
}
