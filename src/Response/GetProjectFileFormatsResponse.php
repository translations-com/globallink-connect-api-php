<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

use GlobalLink\RestClient\Model\FileFormatProfile;

/**
 * Response from getting project file formats.
 */
readonly class GetProjectFileFormatsResponse
{
    /**
     * @param list<FileFormatProfile> $fileFormatProfiles
     */
    public function __construct(
        public array $fileFormatProfiles = [],
    ) {
    }

    /**
     * Create a response from a JSON string.
     *
     * @return list<FileFormatProfile>
     */
    public static function fromJson(string $json): array
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        $data = json_decode($json, true);
        $profiles = [];

        if (is_array($data)) {
            foreach ($data as $ffp) {
                $profiles[] = FileFormatProfile::fromArray($ffp);
            }
        }

        return $profiles;
    }
}
