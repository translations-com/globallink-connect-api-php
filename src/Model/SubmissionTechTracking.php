<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Tech tracking information attached to a submission (read-only response model).
 */
readonly class SubmissionTechTracking
{
    public function __construct(
        public ?string $adaptorName = null,
        public ?string $adaptorVersion = null,
        public ?string $clientVersion = null,
        public ?string $technologyProduct = null,
    ) {
    }

    /**
     * Create a SubmissionTechTracking from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            adaptorName: $data['adaptorName'] ?? null,
            adaptorVersion: $data['adaptorVersion'] ?? null,
            clientVersion: $data['clientVersion'] ?? null,
            technologyProduct: $data['technologyProduct'] ?? null,
        );
    }
}
