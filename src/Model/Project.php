<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents a project in GlobalLink.
 */
readonly class Project
{
    public function __construct(
        public string $name,
        public int $projectId,
        public int $organizationId,
        public string $shortCode,
        public bool $enabled = false,
        public bool $quoteEnabled = false,
        public string $quoteScope = '',
        public bool $autoQuote = false,
        public int $costScopeId = 0,
        public bool $autoStartSubmission = false,
        public int $defaultWorkflowDefinitionId = 0,
        public bool $displayMslaLevel = false,
    ) {
    }

    /**
     * Create a Project from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            projectId: $data['projectId'] ?? 0,
            organizationId: $data['organizationId'] ?? 0,
            shortCode: $data['shortCode'] ?? '',
            enabled: $data['enabled'] ?? false,
            quoteEnabled: $data['quoteEnabled'] ?? false,
            quoteScope: $data['quoteScope'] ?? '',
            autoQuote: $data['autoQuote'] ?? false,
            costScopeId: $data['costScopeId'] ?? 0,
            autoStartSubmission: $data['autoStartSubmission'] ?? false,
            defaultWorkflowDefinitionId: $data['defaultWorkflowDefinitionId'] ?? 0,
            displayMslaLevel: $data['displayMslaLevel'] ?? false,
        );
    }
}
