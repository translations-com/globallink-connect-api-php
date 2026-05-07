<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Response;

use TransPerfect\GlobalLink\Model\CustomAttribute;

/**
 * Response from getting project custom attributes.
 */
readonly class GetProjectCustomAttributesResponse
{
    /**
     * @param list<CustomAttribute> $customAttributes
     */
    public function __construct(
        public array $customAttributes = [],
    ) {
    }

    /**
     * Create a response from a JSON string.
     *
     * @return list<CustomAttribute>
     */
    public static function fromJson(string $json): array
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        $data = json_decode($json, true);
        $attributes = [];

        if (is_array($data)) {
            foreach ($data as $attr) {
                $attributes[] = CustomAttribute::fromArray($attr);
            }
        }

        return $attributes;
    }
}
