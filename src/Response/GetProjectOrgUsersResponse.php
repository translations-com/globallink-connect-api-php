<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Response;

use TransPerfect\GlobalLink\Model\User;

/**
 * Response from getting project organization users.
 */
readonly class GetProjectOrgUsersResponse
{
    /**
     * @param list<User> $users
     */
    public function __construct(
        public array $users = [],
    ) {
    }

    /**
     * Create a response from a JSON string.
     *
     * @return list<User>
     */
    public static function fromJson(string $json): array
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        $data = json_decode($json, true);
        $users = [];

        if (is_array($data)) {
            foreach ($data as $user) {
                $users[] = User::fromArray($user);
            }
        }

        return $users;
    }
}
