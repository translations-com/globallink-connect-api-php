<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents due date information for a submission phase.
 */
readonly class SubmissionDueDateInfo
{
    public function __construct(
        public bool $completed,
        public string $phaseName,
        public int $dueDate,
    ) {
    }

    /**
     * Create a SubmissionDueDateInfo from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            completed: $data['completed'] ?? false,
            phaseName: $data['phaseName'] ?? '',
            dueDate: $data['dueDate'] ?? 0,
        );
    }
}
