<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

use GlobalLink\RestClient\Model\MslaLevel;

/**
 * Response for GET /projects/{project-id}/mslalevels.
 */
final class GetProjectMslaLevelsResponse
{
    /**
     * Parse JSON response into MslaLevel list.
     *
     * @return list<MslaLevel>
     * @throws \InvalidArgumentException If JSON is invalid
     */
    public static function fromJson(string $json): array
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        /** @var list<array<string, mixed>> $data */
        $data = json_decode($json, true) ?? [];

        return array_map(
            fn(array $item) => MslaLevel::fromArray($item),
            $data
        );
    }
}
