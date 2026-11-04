<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents batch information within a submission.
 */
readonly class SubmissionBatchInfo
{
    /**
     * @param list<SubmissionTarget> $targets
     * @param list<SubmissionPhaseInfo> $phaseInfos
     * @param list<SubmissionTargetLanguageInfo> $targetLanguageInfos
     */
    public function __construct(
        public string $name,
        public int $batchId,
        public int $wordCount = 0,
        public string $targetFormat = '',
        public int $workflowId = 0,
        public string $workflowName = '',
        public array $targets = [],
        public array $phaseInfos = [],
        public array $targetLanguageInfos = [],
    ) {
    }

    /**
     * Create a SubmissionBatchInfo from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $targets = [];
        if (!empty($data['targets']) && is_array($data['targets'])) {
            foreach ($data['targets'] as $t) {
                $targets[] = SubmissionTarget::fromArray($t);
            }
        }

        $phaseInfos = [];
        if (!empty($data['phaseInfos']) && is_array($data['phaseInfos'])) {
            foreach ($data['phaseInfos'] as $pi) {
                $phaseInfos[] = SubmissionPhaseInfo::fromArray($pi);
            }
        }

        $targetLanguageInfos = [];
        if (!empty($data['targetLanguageInfos']) && is_array($data['targetLanguageInfos'])) {
            foreach ($data['targetLanguageInfos'] as $tli) {
                $targetLanguageInfos[] = SubmissionTargetLanguageInfo::fromArray($tli);
            }
        }

        return new self(
            name: $data['name'] ?? '',
            batchId: $data['batchId'] ?? 0,
            wordCount: $data['wordCount'] ?? 0,
            targetFormat: $data['targetFormat'] ?? '',
            workflowId: $data['workflowId'] ?? 0,
            workflowName: $data['workflowName'] ?? '',
            targets: $targets,
            phaseInfos: $phaseInfos,
            targetLanguageInfos: $targetLanguageInfos,
        );
    }
}
