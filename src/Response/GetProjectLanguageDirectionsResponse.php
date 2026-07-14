<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

use GlobalLink\RestClient\Model\ProjectLanguageDirection;

/**
 * Response from getting project language directions.
 */
readonly class GetProjectLanguageDirectionsResponse
{
    /**
     * @param list<ProjectLanguageDirection> $languageDirections
     */
    public function __construct(
        public array $languageDirections = [],
    ) {
    }

    /**
     * Create a response from a JSON string.
     *
     * @return list<ProjectLanguageDirection>
     */
    public static function fromJson(string $json): array
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        $data = json_decode($json, true);
        $directions = [];

        if (is_array($data)) {
            foreach ($data as $direction) {
                $directions[] = ProjectLanguageDirection::fromArray($direction);
            }
        }

        return $directions;
    }
}
