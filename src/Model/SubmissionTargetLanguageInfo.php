<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents target language information within a submission batch.
 */
readonly class SubmissionTargetLanguageInfo
{
    /**
     * @param list<SubmissionDueDateInfo> $dueDateInfos
     */
    public function __construct(
        public string $operation,
        public string $targetLanguageDisplayName,
        public int $dueDate,
        public string $targetLanguage,
        public array $dueDateInfos = [],
    ) {
    }

    /**
     * Create a SubmissionTargetLanguageInfo from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $dueDateInfos = [];
        if (!empty($data['dueDateInfos']) && is_array($data['dueDateInfos'])) {
            foreach ($data['dueDateInfos'] as $ddi) {
                $dueDateInfos[] = SubmissionDueDateInfo::fromArray($ddi);
            }
        }

        return new self(
            operation: $data['operation'] ?? '',
            targetLanguageDisplayName: $data['targetLanguageDisplayName'] ?? '',
            dueDate: $data['dueDate'] ?? 0,
            targetLanguage: $data['targetLanguage'] ?? '',
            dueDateInfos: $dueDateInfos,
        );
    }
}
