<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

use GlobalLink\RestClient\Model\MslaLevel;

/**
 * Response for GET /projects/{project-id}/mslalevels.
 */
readonly class GetProjectMslaLevelsResponse extends ApiResponse
{
    /**
     * @param list<MslaLevel> $mslaLevels
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public array $mslaLevels = [],
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
        if (!is_array($data) || self::hasNonArrayItem($data)) {
            return new self(
                responseCode: $responseCode,
                errorMessage: 'Unexpected JSON response',
                headers: $headers,
                responseBody: $json,
            );
        }

        /** @var list<array<string, mixed>> $data */
        $mslaLevels = array_map(
            fn(array $item) => MslaLevel::fromArray($item),
            $data
        );

        return new self(
            mslaLevels: $mslaLevels,
            responseCode: $responseCode,
            headers: $headers,
            responseBody: $json,
        );
    }
}
