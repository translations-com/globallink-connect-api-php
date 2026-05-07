<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Request;

/**
 * Request for cancelling a submission.
 */
final readonly class CancelSubmissionRequest
{
    /**
     * @param list<int>|null $targetIds Target IDs to cancel
     * @param list<int>|null $documentIds Document IDs to cancel
     */
    public function __construct(
        public ?array $targetIds = null,
        public ?array $documentIds = null,
    ) {
    }

    /**
     * Convert to array format for API request.
     *
     * @return array<string, list<int>>
     */
    public function toArray(): array
    {
        if ($this->targetIds !== null) {
            return ['targetIds' => $this->targetIds];
        }
        if ($this->documentIds !== null) {
            return ['documentIds' => $this->documentIds];
        }
        return [];
    }
}
