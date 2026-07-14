<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

use GlobalLink\RestClient\Model\SubmissionWordcount;

/**
 * Response for GET /submissions/{submission-id}/wordcount.
 */
final class GetSubmissionWordcountResponse
{
    /**
     * Parse JSON response into SubmissionWordcount.
     *
     * @throws \InvalidArgumentException If JSON is invalid
     */
    public static function fromJson(string $json): SubmissionWordcount
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($json, true) ?? [];

        return SubmissionWordcount::fromArray($data);
    }
}
