<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Model;

/**
 * Represents a custom attribute definition for a project.
 */
readonly class CustomAttribute
{
    /**
     * @param list<string> $values Allowed values for the attribute (if applicable)
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $description = '',
        public bool $mandatory = false,
        public array $values = [],
    ) {
    }

    /**
     * Create a CustomAttribute from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            type: $data['type'] ?? '',
            description: $data['description'] ?? '',
            mandatory: $data['mandatory'] ?? false,
            values: $data['values'] ?? [],
        );
    }
}
