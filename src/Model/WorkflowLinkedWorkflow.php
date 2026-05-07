<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Model;

/**
 * Represents a linked workflow within a workflow definition.
 */
readonly class WorkflowLinkedWorkflow
{
    public function __construct(
        public string $name,
        public int $id,
        public ?string $description = null,
    ) {
    }

    /**
     * Create a WorkflowLinkedWorkflow from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            id: $data['id'] ?? 0,
            description: $data['description'] ?? null,
        );
    }
}
