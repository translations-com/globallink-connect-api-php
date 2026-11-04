<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

use GlobalLink\RestClient\Model\CustomAttribute;

/**
 * Response from getting project custom attributes.
 */
readonly class GetProjectCustomAttributesResponse extends ApiResponse
{
    /**
     * @param list<CustomAttribute> $customAttributes
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public array $customAttributes = [],
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
        $attributes = [];

        if (!is_array($data) || self::hasNonArrayItem($data)) {
            return new self(
                responseCode: $responseCode,
                errorMessage: 'Unexpected JSON response',
                headers: $headers,
                responseBody: $json,
            );
        }

        foreach ($data as $attr) {
            $attributes[] = CustomAttribute::fromArray($attr);
        }

        return new self(
            customAttributes: $attributes,
            responseCode: $responseCode,
            headers: $headers,
            responseBody: $json,
        );
    }
}
