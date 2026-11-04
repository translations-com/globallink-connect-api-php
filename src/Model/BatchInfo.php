<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

use GlobalLink\RestClient\Exception\InvalidArgumentException;

/**
 * Batch information for creating submissions.
 */
final readonly class BatchInfo
{
    /**
     * @param string $name Name of the batch (max 64, '/' not allowed)
     * @param string $targetFormat Target format: TXLF|NON_PARSABLE
     * @param int|null $workflowId Optional workflow ID (legacy, use workflowDefinitionId)
     * @param int|null $workflowDefinitionId Workflow definition ID
     * @param list<string>|null $targetLanguages Target language codes (convenience property)
     * @param list<CreateSubmissionTargetLanguageInfo> $targetLanguageInfos Target language information (detailed)
     */
    public function __construct(
        public string $name,
        public string $targetFormat = 'TXLF',
        public ?int $workflowId = null,
        public ?int $workflowDefinitionId = null,
        public ?array $targetLanguages = null,
        public array $targetLanguageInfos = [],
    ) {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Batch name cannot be empty');
        }
        $validFormats = ['TXLF', 'NON_PARSABLE'];
        if (!in_array($targetFormat, $validFormats, true)) {
            throw new InvalidArgumentException(
                'targetFormat must be one of: ' . implode(', ', $validFormats)
            );
        }
    }

    /**
     * Convert to array format for API requests.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $targetLanguageInfos = $this->targetLanguageInfos;
        if (empty($targetLanguageInfos) && !empty($this->targetLanguages)) {
            foreach ($this->targetLanguages as $lang) {
                $targetLanguageInfos[] = new CreateSubmissionTargetLanguageInfo($lang);
            }
        }

        $data = [
            'name' => $this->name,
            'targetFormat' => $this->targetFormat,
            'targetLanguageInfos' => array_map(
                fn(CreateSubmissionTargetLanguageInfo $tli) => $tli->toArray(),
                $targetLanguageInfos
            ),
        ];

        if ($this->workflowDefinitionId !== null) {
            $data['workflowDefinitionId'] = $this->workflowDefinitionId;
        } elseif ($this->workflowId !== null) {
            $data['workflowId'] = $this->workflowId;
        }

        return $data;
    }
}
