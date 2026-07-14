<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

/**
 * Response from OAuth token authentication.
 */
readonly class LoginResponse
{
    public function __construct(
        public ?string $accessToken = null,
        public ?string $tokenType = null,
        public ?int $expiresIn = null,
        public ?string $scope = null,
        public ?string $refreshToken = null,
    ) {
    }

    /**
     * Create a LoginResponse from a JSON string.
     */
    public static function fromJson(string $json): self
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        $data = json_decode($json, true);
        return self::fromArray($data);
    }

    /**
     * Create a LoginResponse from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            accessToken: $data['access_token'] ?? null,
            tokenType: $data['token_type'] ?? null,
            expiresIn: $data['expires_in'] ?? null,
            scope: $data['scope'] ?? null,
            refreshToken: $data['refresh_token'] ?? null,
        );
    }
}
