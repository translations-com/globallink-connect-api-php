<?php

declare(strict_types=1);

/**
 * Example: Working with Projects and Instance Information
 *
 * This example demonstrates how to:
 * - Check instance health
 * - Initialize the GlobalLink client
 * - Get instance information
 * - List all projects
 * - Get project details including:
 *   - File formats
 *   - Workflows
 *   - Language directions
 *   - Custom attributes
 *   - Organization users
 *   - MSLA (priority) levels
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use TransPerfect\GlobalLink\GlobalLinkClient;
use TransPerfect\GlobalLink\Request\ListProjectsRequest;
use TransPerfect\GlobalLink\Exception\GlobalLinkException;

// Load environment variables (in production, use a proper env loader)
$apiUrl = getenv('GLE_API_URL') ?: '';
$username = getenv('GLE_USERNAME') ?: '';
$password = getenv('GLE_PASSWORD') ?: '';
$oauthClient = getenv('GLE_OAUTH_CLIENT') ?: '';
$oauthSecret = getenv('GLE_OAUTH_SECRET') ?: '';

if (empty($apiUrl) || empty($username) || empty($password) || empty($oauthClient) || empty($oauthSecret)) {
    die("Error: Missing required configuration. Set GLE_* environment variables.\n");
}

try {
    echo "=== Pre-Connection Health Check ===\n";

    // Create a temporary client just for healthcheck
    // Note: healthcheck doesn't require authentication
    $client = new GlobalLinkClient(
        $apiUrl,
        $username,
        $password,
        $oauthClient,
        $oauthSecret
    );

    $health = $client->healthcheck();
    if ($health->healthy) {
        echo "Instance is healthy: {$health->message}\n\n";
    } else {
        die("Instance is not healthy: {$health->message}\n");
    }

    echo "Connected successfully!\n\n";

    // Get instance info
    echo "=== Instance Info ===\n";
    $instanceInfo = $client->getInstanceInfo();
    echo "Version: {$instanceInfo->version}\n";
    echo "Max upload size: " . number_format($instanceInfo->maxUploadSize) . " bytes\n";
    echo "Max documents per submission: {$instanceInfo->maxDocumentsBeforeNewSubmission}\n\n";

    // List all projects
    echo "=== Projects ===\n";
    $projects = $client->getProjects();
    echo "Found " . count($projects) . " project(s)\n\n";

    foreach ($projects as $project) {
        echo "Project: {$project->name} ({$project->shortCode})\n";
        echo "  ID: {$project->projectId}\n";
        echo "  Organization ID: {$project->organizationId}\n";
        echo "  Enabled: " . ($project->enabled ? 'Yes' : 'No') . "\n";
        echo "  Auto-start: " . ($project->autoStartSubmission ? 'Yes' : 'No') . "\n";
        echo "\n";
    }

    // Get detailed info for the first project
    if (!empty($projects)) {
        $projectId = $projects[0]->projectId;
        echo "=== Project Details (ID: {$projectId}) ===\n";

        $projectInfo = $client->getProject($projectId);
        echo "Name: {$projectInfo->name}\n";
        echo "Short Code: {$projectInfo->shortCode}\n\n";

        // File formats
        echo "File Formats:\n";
        $fileFormats = $client->getProjectFileFormats($projectId);
        foreach ($fileFormats as $format) {
            $default = $format->default ? ' (default)' : '';
            echo "  - {$format->name}{$default}\n";
        }
        echo "\n";

        // Workflows
        echo "Workflows:\n";
        $workflows = $client->getProjectWorkflows($projectId);
        foreach ($workflows as $workflow) {
            $default = $workflow->default ? ' (default)' : '';
            echo "  - {$workflow->name}{$default}\n";
        }
        echo "\n";

        // Language directions
        echo "Language Directions:\n";
        $directions = $client->getProjectLanguageDirections($projectId);
        foreach ($directions as $direction) {
            echo "  - {$direction->sourceLanguageDisplayName} -> {$direction->targetLanguageDisplayName}\n";
        }
        echo "\n";

        // Custom attributes
        echo "Custom Attributes:\n";
        $attributes = $client->getProjectCustomAttributes($projectId);
        if (empty($attributes)) {
            echo "  (none)\n";
        } else {
            foreach ($attributes as $attr) {
                $mandatory = $attr->mandatory ? ' (required)' : '';
                echo "  - {$attr->name} ({$attr->type}){$mandatory}\n";
            }
        }
        echo "\n";

        // Organization users
        echo "Organization Users:\n";
        $users = $client->getProjectOrgUsers($projectId);
        if (empty($users)) {
            echo "  (none)\n";
        } else {
            $displayCount = min(5, count($users));
            for ($i = 0; $i < $displayCount; $i++) {
                $user = $users[$i];
                $enabled = $user->enabled ? '' : ' (disabled)';
                echo "  - {$user->userName} ({$user->email}){$enabled}\n";
            }
            if (count($users) > 5) {
                echo "  ... and " . (count($users) - 5) . " more\n";
            }
        }
        echo "\n";

        // MSLA (priority) levels
        echo "MSLA Levels (Priorities):\n";
        $mslaLevels = $client->getProjectMslaLevels($projectId);
        if (empty($mslaLevels)) {
            echo "  (none configured)\n";
        } else {
            foreach ($mslaLevels as $level) {
                $default = $level->default ? ' (default)' : '';
                echo "  - {$level->name} [{$level->priority}]{$default}\n";
                echo "    ID: {$level->mslaLevelId}\n";
            }
        }
        echo "\n";
    }

    // Example with filters
    // Note: pageSize must be between 20 and 200 (API requirement)
    echo "=== Filtered Projects ===\n";
    $request = new ListProjectsRequest(pageSize: 20);
    $filteredProjects = $client->getProjects($request);
    echo "Retrieved " . count($filteredProjects) . " project(s) with page size 20\n";

} catch (GlobalLinkException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
