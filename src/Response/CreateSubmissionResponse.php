<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Response;

/**
 * Response from creating a submission.
 */
readonly class CreateSubmissionResponse
{
    /**
     * @param list<string> $messages
     */
    public function __construct(
        public int $submissionId = 0,
        public array $messages = [],
    ) {
    }

    /**
     * Create a CreateSubmissionResponse from a JSON string.
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
     * Create a CreateSubmissionResponse from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            submissionId: $data['submissionId'] ?? 0,
            messages: $data['messages'] ?? [],
        );
    }
}
