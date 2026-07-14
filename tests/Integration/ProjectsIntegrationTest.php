<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Tests\Integration;

use GlobalLink\RestClient\Model\CustomAttribute;
use GlobalLink\RestClient\Model\FileFormatProfile;
use GlobalLink\RestClient\Model\MslaLevel;
use GlobalLink\RestClient\Model\Project;
use GlobalLink\RestClient\Model\ProjectInfo;
use GlobalLink\RestClient\Model\ProjectLanguageDirection;
use GlobalLink\RestClient\Model\User;
use GlobalLink\RestClient\Model\WorkflowDefinition;
use GlobalLink\RestClient\Response\HealthcheckResponse;
use GlobalLink\RestClient\Response\InstanceInfoResponse;

/**
 * Integration tests for project-related operations.
 */
class ProjectsIntegrationTest extends IntegrationTestCase
{
    public function testGetProjects(): void
    {
        $this->debug('Fetching all projects...');
        $projects = $this->client->getProjects();

        $this->assertIsArray($projects);
        $this->assertNotEmpty($projects, 'Expected at least one project');
        $this->debug('Found ' . count($projects) . ' projects');

        foreach ($projects as $project) {
            $this->assertInstanceOf(Project::class, $project);
            $this->assertNotEmpty($project->name);
            $this->assertGreaterThan(0, $project->projectId);
        }
        $this->debug('First project: ' . $projects[0]->name . ' (ID: ' . $projects[0]->projectId . ')');
    }

    public function testGetProject(): void
    {
        $projectId = $this->getTestProjectId();
        $this->debug("Fetching project details for ID: {$projectId}");

        $project = $this->client->getProject($projectId);

        $this->assertInstanceOf(ProjectInfo::class, $project);
        $this->assertSame($projectId, $project->projectId);
        $this->assertNotEmpty($project->name);
        $this->assertNotEmpty($project->shortCode);
        $this->debug("Project: {$project->name} ({$project->shortCode})");
    }

    public function testGetProjectFileFormats(): void
    {
        $projectId = $this->getTestProjectId();
        $this->debug("Fetching file formats for project {$projectId}...");

        $formats = $this->client->getProjectFileFormats($projectId);

        $this->assertIsArray($formats);
        $this->assertNotEmpty($formats, 'Expected at least one file format');
        $this->debug('Found ' . count($formats) . ' file formats');

        foreach ($formats as $format) {
            $this->assertInstanceOf(FileFormatProfile::class, $format);
            $this->assertNotEmpty($format->name);
            $this->assertGreaterThan(0, $format->fileFormatId);
        }
        $formatNames = array_map(fn($f) => $f->name, array_slice($formats, 0, 3));
        $this->debug('Formats: ' . implode(', ', $formatNames) . (count($formats) > 3 ? '...' : ''));
    }

    public function testGetProjectWorkflows(): void
    {
        $projectId = $this->getTestProjectId();
        $this->debug("Fetching workflows for project {$projectId}...");

        $workflows = $this->client->getProjectWorkflows($projectId);

        $this->assertIsArray($workflows);
        $this->assertNotEmpty($workflows, 'Expected at least one workflow');
        $this->debug('Found ' . count($workflows) . ' workflows');

        foreach ($workflows as $workflow) {
            $this->assertInstanceOf(WorkflowDefinition::class, $workflow);
            $this->assertNotEmpty($workflow->name);
            $this->assertGreaterThan(0, $workflow->id);
        }
        $workflowNames = array_map(fn($w) => $w->name, $workflows);
        $this->debug('Workflows: ' . implode(', ', $workflowNames));
    }

    public function testGetProjectCustomAttributes(): void
    {
        $projectId = $this->getTestProjectId();
        $this->debug("Fetching custom attributes for project {$projectId}...");

        $attributes = $this->client->getProjectCustomAttributes($projectId);

        $this->assertIsArray($attributes);
        $this->debug('Found ' . count($attributes) . ' custom attributes');

        foreach ($attributes as $attribute) {
            $this->assertInstanceOf(CustomAttribute::class, $attribute);
            $this->assertNotEmpty($attribute->name);
            $this->assertNotEmpty($attribute->type);
        }
        if (!empty($attributes)) {
            $attrNames = array_map(fn($a) => $a->name, array_slice($attributes, 0, 3));
            $this->debug('Attributes: ' . implode(', ', $attrNames));
        }
    }

    public function testGetProjectLanguageDirections(): void
    {
        $projectId = $this->getTestProjectId();
        $this->debug("Fetching language directions for project {$projectId}...");

        $directions = $this->client->getProjectLanguageDirections($projectId);

        $this->assertIsArray($directions);
        $this->assertNotEmpty($directions, 'Expected at least one language direction');
        $this->debug('Found ' . count($directions) . ' language directions');

        foreach ($directions as $direction) {
            $this->assertInstanceOf(ProjectLanguageDirection::class, $direction);
            $this->assertNotEmpty($direction->sourceLanguage);
            $this->assertNotEmpty($direction->targetLanguage);
        }
        $dirDisplay = array_map(
            fn($d) => "{$d->sourceLanguage} → {$d->targetLanguage}",
            array_slice($directions, 0, 3)
        );
        $this->debug('Directions: ' . implode(', ', $dirDisplay) . (count($directions) > 3 ? '...' : ''));
    }

    public function testGetProjectOrgUsers(): void
    {
        $projectId = $this->getTestProjectId();
        $this->debug("Fetching organization users for project {$projectId}...");

        $users = $this->client->getProjectOrgUsers($projectId);

        $this->assertIsArray($users);
        $this->debug('Found ' . count($users) . ' users');

        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertGreaterThan(0, $user->userId);
        }
    }

    public function testGetInstanceInfo(): void
    {
        $this->debug('Fetching instance info...');
        $info = $this->client->getInstanceInfo();

        $this->assertInstanceOf(InstanceInfoResponse::class, $info);
        $this->assertNotEmpty($info->version);
        $this->assertGreaterThan(0, $info->maxUploadSize);
        $this->assertGreaterThan(0, $info->maxDocumentsBeforeNewSubmission);
        $this->debug("Version: {$info->version}, Max upload: " . number_format($info->maxUploadSize) . ' bytes');
    }

    public function testGetProjectMslaLevels(): void
    {
        $projectId = $this->getTestProjectId();
        $this->debug("Fetching MSLA levels for project {$projectId}...");

        $levels = $this->client->getProjectMslaLevels($projectId);

        $this->assertIsArray($levels);
        $this->debug('Found ' . count($levels) . ' MSLA levels');

        foreach ($levels as $level) {
            $this->assertInstanceOf(MslaLevel::class, $level);
            $this->assertGreaterThan(0, $level->mslaLevelId);
            $this->assertNotEmpty($level->name);
            $this->assertNotEmpty($level->priority);
        }

        if (!empty($levels)) {
            $levelNames = array_map(fn($l) => "{$l->name} ({$l->priority})", $levels);
            $this->debug('Levels: ' . implode(', ', $levelNames));
            $defaultLevels = array_filter($levels, fn($l) => $l->default === true);
            $this->assertIsArray($defaultLevels);
        }
    }

    public function testHealthcheck(): void
    {
        $this->debug('Performing healthcheck...');
        $response = $this->client->healthcheck();

        $this->assertInstanceOf(HealthcheckResponse::class, $response);
        $this->assertTrue($response->healthy);
        $this->assertSame('OK', $response->message);
        $this->debug("Health: {$response->message}");
    }
}
