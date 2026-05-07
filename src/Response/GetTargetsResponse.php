<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Response;

use TransPerfect\GlobalLink\Model\Target;

/**
 * Response from getting targets.
 */
readonly class GetTargetsResponse
{
    /**
     * @param list<Target> $targets
     */
    public function __construct(
        public array $targets = [],
    ) {
    }

    /**
     * Create a response from a JSON string.
     *
     * @return list<Target>
     */
    public static function fromJson(string $json): array
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        $data = json_decode($json, true);
        $targets = [];

        if (is_array($data)) {
            foreach ($data as $item) {
                $targets[] = Target::fromArray($item);
            }
        }

        return $targets;
    }
}
