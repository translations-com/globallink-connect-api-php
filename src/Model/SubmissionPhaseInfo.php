<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents phase information for a submission batch.
 */
readonly class SubmissionPhaseInfo
{
    /**
     * @param list<string> $availableEditors
     */
    public function __construct(
        public string $phaseName,
        public array $availableEditors = [],
        public bool $useProtectedPayload = false,
        public bool $budgetable = false,
    ) {
    }

    /**
     * Create a SubmissionPhaseInfo from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            phaseName: $data['phaseName'] ?? '',
            availableEditors: $data['availableEditors'] ?? [],
            useProtectedPayload: $data['useProtectedPayload'] ?? false,
            budgetable: $data['budgetable'] ?? false,
        );
    }
}
