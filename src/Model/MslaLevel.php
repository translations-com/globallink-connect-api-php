<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents an MSLA (Master Service Level Agreement) level for a project.
 *
 * MSLA levels define submission priorities (e.g., Rush, Standard, Low).
 */
readonly class MslaLevel
{
    public function __construct(
        public int $mslaLevelId,
        public string $name,
        public string $priority,
        public bool $default = false,
        public ?string $description = null,
        public ?string $resourceId = null,
    ) {
    }

    /**
     * Create an MslaLevel from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            mslaLevelId: $data['mslaLevelId'] ?? 0,
            name: $data['name'] ?? '',
            priority: $data['priority'] ?? '',
            default: $data['default'] ?? false,
            description: $data['description'] ?? null,
            resourceId: $data['resourceId'] ?? null,
        );
    }
}
