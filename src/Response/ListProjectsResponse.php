<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Response;

use GlobalLink\RestClient\Model\Project;

/**
 * Response from listing projects.
 */
readonly class ListProjectsResponse extends ApiResponse
{
    /**
     * @param list<Project> $projects
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public array $projects = [],
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

        // If response wraps in "projects" key, use it; else assume the array itself.
        $projectsList = is_array($data) ? ($data['projects'] ?? $data) : null;
        if (!is_array($projectsList) || self::hasNonArrayItem($projectsList)) {
            return new self(
                responseCode: $responseCode,
                errorMessage: 'Unexpected JSON response',
                headers: $headers,
                responseBody: $json,
            );
        }

        $projects = [];
        foreach ($projectsList as $projectData) {
            $projects[] = Project::fromArray($projectData);
        }

        return new self(
            projects: $projects,
            responseCode: $responseCode,
            headers: $headers,
            responseBody: $json,
        );
    }
}
