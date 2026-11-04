<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Response from OAuth token authentication.
 */
readonly class LoginResponse extends ApiResponse
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public ?string $accessToken = null,
        public ?string $tokenType = null,
        public ?int $expiresIn = null,
        public ?string $scope = null,
        public ?string $refreshToken = null,
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

        $unusable = self::unusableSuccessBodyMessage($json, allowEmpty: false);
        if ($unusable !== null) {
            return new self(
                responseCode: $responseCode,
                errorMessage: $unusable,
                headers: $headers,
                responseBody: $json,
            );
        }

        $data = json_decode($json, true);
        return self::fromArray($data, $responseCode, $headers, $json);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $headers
     */
    public static function fromArray(
        array $data,
        int $responseCode = 200,
        array $headers = [],
        string $responseBody = '',
    ): self {
        return new self(
            accessToken: $data['access_token'] ?? null,
            tokenType: $data['token_type'] ?? null,
            expiresIn: $data['expires_in'] ?? null,
            scope: $data['scope'] ?? null,
            refreshToken: $data['refresh_token'] ?? null,
            responseCode: $responseCode,
            headers: $headers,
            responseBody: $responseBody,
        );
    }
}
