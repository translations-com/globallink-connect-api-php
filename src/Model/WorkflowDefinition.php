<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents a workflow definition in GlobalLink.
 */
readonly class WorkflowDefinition
{
    /**
     * @param list<WorkflowLinkedWorkflow> $linkedWorkflows
     */
    public function __construct(
        public string $name,
        public int $id,
        public bool $default = false,
        public ?string $description = null,
        public array $linkedWorkflows = [],
    ) {
    }

    /**
     * Create a WorkflowDefinition from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $linkedWorkflows = [];
        if (!empty($data['linkedWorkflows']) && is_array($data['linkedWorkflows'])) {
            foreach ($data['linkedWorkflows'] as $lw) {
                $linkedWorkflows[] = WorkflowLinkedWorkflow::fromArray($lw);
            }
        }

        return new self(
            name: $data['name'] ?? '',
            id: $data['id'] ?? 0,
            default: $data['default'] ?? false,
            description: $data['description'] ?? null,
            linkedWorkflows: $linkedWorkflows,
        );
    }
}
