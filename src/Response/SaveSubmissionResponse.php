<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Response from saving a submission.
 */
readonly class SaveSubmissionResponse
{
    /**
     * @param list<string> $messages
     * @param list<int> $startedSubmissionIds
     * @param list<int> $linkedSubmissionIds
     */
    public function __construct(
        public array $messages = [],
        public array $startedSubmissionIds = [],
        public array $linkedSubmissionIds = [],
    ) {
    }

    /**
     * Create a SaveSubmissionResponse from a JSON string.
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
     * Create a SaveSubmissionResponse from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            messages: $data['messages'] ?? [],
            startedSubmissionIds: $data['startedSubmissionIds'] ?? [],
            linkedSubmissionIds: $data['linkedSubmissionIds'] ?? [],
        );
    }
}
