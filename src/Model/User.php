<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents a user in GlobalLink.
 */
readonly class User
{
    public function __construct(
        public int $userId,
        public string $userName,
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $userType = '',
        public bool $enabled = false,
        public bool $ssoUser = false,
    ) {
    }

    /**
     * Create a User from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['userId'] ?? 0,
            userName: $data['userName'] ?? '',
            firstName: $data['firstName'] ?? '',
            lastName: $data['lastName'] ?? '',
            email: $data['email'] ?? '',
            userType: $data['userType'] ?? '',
            enabled: $data['enabled'] ?? false,
            ssoUser: $data['ssoUser'] ?? false,
        );
    }
}
