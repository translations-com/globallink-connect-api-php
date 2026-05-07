<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Request;

use TransPerfect\GlobalLink\Exception\InvalidArgumentException;
use TransPerfect\GlobalLink\Model\BatchInfo;
use TransPerfect\GlobalLink\Model\TechTracking;

/**
 * Request for creating a new submission.
 */
final readonly class CreateSubmissionRequest
{
    /**
     * @param string $name Submission name (max 255, restricted chars replaced with _)
     * @param int $dueDate Due date in Unix milliseconds
     * @param int $projectId Project ID
     * @param string $sourceLanguage Source language code
     * @param list<BatchInfo> $batchInfos Batch information (at least 1 required)
     * @param string $claimScope Claim scope: LANGUAGE|BATCH|FILE
     * @param string|null $instructions Instructions (max 1MB)
     * @param string|null $background Background information (max 1MB)
     * @param string|null $clientIdentifier Client identifier (max 255)
     * @param list<int>|null $owners Owner user IDs
     * @param int|null $mslaLevelId MSLA level ID
     * @param bool|null $requestQuote Request quote
     * @param array<string, string>|null $metadata Metadata (key max 255, value max 2000, key no '/')
     * @param array<string, string>|null $customAttributes Custom attributes (name a-zA-Z0-9_- max 255, value max 255)
     * @param int|null $paClientId PA client ID (internal)
     * @param string|null $paJobNumber PA job number (internal)
     */
    public function __construct(
        public string $name,
        public int $dueDate,
        public int $projectId,
        public string $sourceLanguage,
        public array $batchInfos,
        public string $claimScope,
        public ?string $instructions = null,
        public ?string $background = null,
        public ?string $clientIdentifier = null,
        public ?array $owners = null,
        public ?int $mslaLevelId = null,
        public ?bool $requestQuote = null,
        public ?array $metadata = null,
        public ?array $customAttributes = null,
        public ?int $paClientId = null,
        public ?string $paJobNumber = null,
        public ?TechTracking $techTracking = null,
    ) {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Submission name cannot be empty');
        }
        if ($dueDate <= 0) {
            throw new InvalidArgumentException('Due date must be a positive timestamp');
        }
        if ($projectId <= 0) {
            throw new InvalidArgumentException('Project ID must be positive');
        }
        if (empty(trim($sourceLanguage))) {
            throw new InvalidArgumentException('Source language cannot be empty');
        }
        if (empty($batchInfos)) {
            throw new InvalidArgumentException('At least one batch info is required');
        }
        foreach ($batchInfos as $batch) {
            if (!$batch instanceof BatchInfo) {
                throw new InvalidArgumentException('batchInfos must contain BatchInfo instances');
            }
        }
        $validClaimScopes = ['LANGUAGE', 'BATCH', 'FILE'];
        if (!in_array($claimScope, $validClaimScopes, true)) {
            throw new InvalidArgumentException(
                'claimScope must be one of: ' . implode(', ', $validClaimScopes)
            );
        }
    }

    /**
     * Convert to array format for API request.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'dueDate' => $this->dueDate,
            'projectId' => $this->projectId,
            'sourceLanguage' => $this->sourceLanguage,
            'batchInfos' => array_map(fn(BatchInfo $b) => $b->toArray(), $this->batchInfos),
            'claimScope' => $this->claimScope,
        ];

        if ($this->instructions !== null) {
            $data['instructions'] = $this->instructions;
        }
        if ($this->background !== null) {
            $data['background'] = $this->background;
        }
        if ($this->clientIdentifier !== null) {
            $data['clientIdentifier'] = $this->clientIdentifier;
        }
        if ($this->owners !== null) {
            $data['owners'] = implode(',', $this->owners);
        }
        if ($this->mslaLevelId !== null) {
            $data['mslaLevelId'] = $this->mslaLevelId;
        }
        if ($this->requestQuote !== null) {
            $data['requestQuote'] = $this->requestQuote;
        }
        if ($this->metadata !== null) {
            $data['metadata'] = $this->metadata;
        }
        if ($this->customAttributes !== null) {
            $data['customAttributes'] = $this->customAttributes;
        }
        if ($this->paClientId !== null) {
            $data['paClientId'] = $this->paClientId;
        }
        if ($this->paJobNumber !== null) {
            $data['paJobNumber'] = $this->paJobNumber;
        }
        if ($this->techTracking !== null) {
            $data['techTracking'] = $this->techTracking->toArray();
        }

        return $data;
    }
}
