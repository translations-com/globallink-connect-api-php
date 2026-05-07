<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Response;

use TransPerfect\GlobalLink\Model\SubmissionInfo;

/**
 * Response from getting submissions.
 */
readonly class GetSubmissionsResponse
{
    /**
     * @param list<SubmissionInfo> $submissions
     */
    public function __construct(
        public array $submissions = [],
    ) {
    }

    /**
     * Create a response from a JSON string.
     *
     * @return list<SubmissionInfo>
     */
    public static function fromJson(string $json): array
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        $data = json_decode($json, true);
        $submissions = [];

        if (is_array($data)) {
            foreach ($data as $row) {
                $submissions[] = SubmissionInfo::fromArray($row);
            }
        }

        return $submissions;
    }
}
