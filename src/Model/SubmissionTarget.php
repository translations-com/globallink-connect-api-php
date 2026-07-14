<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents a target within a submission batch.
 */
readonly class SubmissionTarget
{
    /**
     * @param list<SubmissionTranslatableName> $translatableNames
     */
    public function __construct(
        public string $status,
        public int $jobId,
        public int $submissionId,
        public int $targetId,
        public string $documentName,
        public int $dueDate,
        public int $documentId,
        public string $fileFormatName,
        public string $sourceLanguage,
        public string $targetLanguage,
        public ?int $dateCompleted = null,
        public int $fileFormatId = 0,
        public string $currentPhase = '',
        public array $translatableNames = [],
    ) {
    }

    /**
     * Create a SubmissionTarget from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $translatableNames = [];
        if (!empty($data['translatableNames']) && is_array($data['translatableNames'])) {
            foreach ($data['translatableNames'] as $tn) {
                $translatableNames[] = SubmissionTranslatableName::fromArray($tn);
            }
        }

        return new self(
            status: $data['status'] ?? '',
            jobId: $data['jobId'] ?? 0,
            submissionId: $data['submissionId'] ?? 0,
            targetId: $data['targetId'] ?? 0,
            documentName: $data['documentName'] ?? '',
            dueDate: $data['dueDate'] ?? 0,
            documentId: $data['documentId'] ?? 0,
            fileFormatName: $data['fileFormatName'] ?? '',
            sourceLanguage: $data['sourceLanguage'] ?? '',
            targetLanguage: $data['targetLanguage'] ?? '',
            dateCompleted: $data['dateCompleted'] ?? null,
            fileFormatId: $data['fileFormatId'] ?? 0,
            currentPhase: $data['currentPhase'] ?? '',
            translatableNames: $translatableNames,
        );
    }
}
