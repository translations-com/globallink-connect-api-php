<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Response;

use TransPerfect\GlobalLink\Model\Project;

/**
 * Response from listing projects.
 */
readonly class ListProjectsResponse
{
    /**
     * @param list<Project> $projects
     */
    public function __construct(
        public array $projects = [],
    ) {
    }

    /**
     * Create a ListProjectsResponse from a JSON string.
     *
     * @return list<Project>
     */
    public static function fromJson(string $json): array
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        $data = json_decode($json, true);

        // If response wraps in "projects" key, use it; else assume the array itself.
        $projectsList = $data['projects'] ?? $data;
        $projects = [];

        if (is_array($projectsList)) {
            foreach ($projectsList as $projectData) {
                $projects[] = Project::fromArray($projectData);
            }
        }

        return $projects;
    }
}
