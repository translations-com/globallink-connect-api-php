<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents detailed project information in GlobalLink.
 */
readonly class ProjectInfo
{
    /**
     * @param list<FileFormatProfile> $fileFormatProfiles
     * @param list<WorkflowDefinition> $workflowDefinitions
     * @param list<ProjectLanguageDirection> $projectLanguageDirections
     */
    public function __construct(
        public string $name,
        public int $projectId,
        public int $organizationId,
        public string $shortCode,
        public bool $enabled = false,
        public bool $autoStartSubmission = false,
        public bool $quoteEnabled = false,
        public ?string $quoteScope = null,
        public bool $autoQuote = false,
        public int $costScopeId = 0,
        public int $defaultWorkflowDefinitionId = 0,
        public array $fileFormatProfiles = [],
        public array $workflowDefinitions = [],
        public array $projectLanguageDirections = [],
    ) {
    }

    /**
     * Create a ProjectInfo from a JSON string.
     */
    public static function fromJson(string $json): self
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        $data = json_decode($json, true);
        return self::fromArray($data);
    }

    /**
     * Create a ProjectInfo from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $fileFormatProfiles = [];
        if (!empty($data['fileFormatProfiles']) && is_array($data['fileFormatProfiles'])) {
            foreach ($data['fileFormatProfiles'] as $ffp) {
                $fileFormatProfiles[] = FileFormatProfile::fromArray($ffp);
            }
        }

        $workflowDefinitions = [];
        if (!empty($data['workflowDefinitions']) && is_array($data['workflowDefinitions'])) {
            foreach ($data['workflowDefinitions'] as $wd) {
                $workflowDefinitions[] = WorkflowDefinition::fromArray($wd);
            }
        }

        $projectLanguageDirections = [];
        if (!empty($data['projectLanguageDirections']) && is_array($data['projectLanguageDirections'])) {
            foreach ($data['projectLanguageDirections'] as $ld) {
                $projectLanguageDirections[] = ProjectLanguageDirection::fromArray($ld);
            }
        }

        return new self(
            name: $data['name'] ?? '',
            projectId: $data['projectId'] ?? 0,
            organizationId: $data['organizationId'] ?? 0,
            shortCode: $data['shortCode'] ?? '',
            enabled: $data['enabled'] ?? false,
            autoStartSubmission: $data['autoStartSubmission'] ?? false,
            quoteEnabled: $data['quoteEnabled'] ?? false,
            quoteScope: $data['quoteScope'] ?? null,
            autoQuote: $data['autoQuote'] ?? false,
            costScopeId: $data['costScopeId'] ?? 0,
            defaultWorkflowDefinitionId: $data['defaultWorkflowDefinitionId'] ?? 0,
            fileFormatProfiles: $fileFormatProfiles,
            workflowDefinitions: $workflowDefinitions,
            projectLanguageDirections: $projectLanguageDirections,
        );
    }
}
