<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents a translatable name in a submission target.
 */
readonly class SubmissionTranslatableName
{
    public function __construct(
        public string $phaseName,
        public string $targetFileName,
    ) {
    }

    /**
     * Create a SubmissionTranslatableName from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            phaseName: $data['phaseName'] ?? '',
            targetFileName: $data['targetFileName'] ?? '',
        );
    }
}
