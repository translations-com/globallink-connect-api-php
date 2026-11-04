<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

use GlobalLink\RestClient\Model\FileFormatProfile;

/**
 * Response from getting project file formats.
 */
readonly class GetProjectFileFormatsResponse extends ApiResponse
{
    /**
     * @param list<FileFormatProfile> $fileFormatProfiles
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public array $fileFormatProfiles = [],
        int $responseCode = 200,
        ?string $errorMessage = null,
        array $headers = [],
        string $responseBody = '',
    ) {
        parent::__construct($responseCode, $errorMessage, $headers, $responseBody);
    }

    /**
     * @param array<string, list<string>> $headers
     */
    public static function fromJson(
        string $json,
        int $responseCode = 200,
        array $headers = [],
    ): self {
        $errorMessage = self::extractErrorMessage($json, $responseCode);
        if ($errorMessage !== null) {
            return new self(
                responseCode: $responseCode,
                errorMessage: $errorMessage,
                headers: $headers,
                responseBody: $json,
            );
        }

        $unusable = self::unusableSuccessBodyMessage($json, allowEmpty: true);
        if ($unusable !== null) {
            return new self(
                responseCode: $responseCode,
                errorMessage: $unusable,
                headers: $headers,
                responseBody: $json,
            );
        }

        if (trim($json) === '') {
            return new self(
                responseCode: $responseCode,
                headers: $headers,
                responseBody: $json,
            );
        }

        $data = json_decode($json, true);
        $profiles = [];

        if (!is_array($data) || self::hasNonArrayItem($data)) {
            return new self(
                responseCode: $responseCode,
                errorMessage: 'Unexpected JSON response',
                headers: $headers,
                responseBody: $json,
            );
        }

        foreach ($data as $ffp) {
            $profiles[] = FileFormatProfile::fromArray($ffp);
        }

        return new self(
            fileFormatProfiles: $profiles,
            responseCode: $responseCode,
            headers: $headers,
            responseBody: $json,
        );
    }
}
