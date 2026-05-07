<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Request;

/**
 * Request for OAuth token authentication.
 *
 * @internal This request is used internally by GlobalLinkClient.
 */
readonly class LoginRequest
{
    public function __construct(
        public string $username,
        #[\SensitiveParameter] public string $password,
        public string $grantType = 'password',
    ) {
    }

    /**
     * Convert to form URL encoded format for OAuth token request.
     *
     * @return array<string, string>
     */
    public function toFormUrlEncoded(): array
    {
        return [
            'grant_type' => $this->grantType,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }
}
