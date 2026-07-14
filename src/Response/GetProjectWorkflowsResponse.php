<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

use GlobalLink\RestClient\Model\WorkflowDefinition;

/**
 * Response from getting project workflows.
 */
readonly class GetProjectWorkflowsResponse
{
    /**
     * @param list<WorkflowDefinition> $workflows
     */
    public function __construct(
        public array $workflows = [],
    ) {
    }

    /**
     * Create a response from a JSON string.
     *
     * @return list<WorkflowDefinition>
     */
    public static function fromJson(string $json): array
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        $data = json_decode($json, true);
        $workflows = [];

        if (is_array($data)) {
            foreach ($data as $workflow) {
                $workflows[] = WorkflowDefinition::fromArray($workflow);
            }
        }

        return $workflows;
    }
}
