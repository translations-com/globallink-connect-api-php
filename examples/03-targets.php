<?php

declare(strict_types=1);

/**
 * Example: Working with Targets
 *
 * This example demonstrates how to:
 * - Poll for processed targets (with pagination)
 * - Download individual translated files
 * - Download bulk deliverables (multiple options)
 * - Confirm delivery
 * - Handle cancelled targets
 *
 * API Pagination Note:
 * - pageSize must be between 20 and 200 (default: 50)
 * - pageNumber is 0-indexed
 *
 * @see https://onlinehelp.transperfect.com/GLE/RESTAPI/5-using-pagination.html
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use GlobalLink\RestClient\GlobalLinkClient;
use GlobalLink\RestClient\Request\GetTargetsRequest;
use GlobalLink\RestClient\Exception\GlobalLinkException;

// Load environment variables
$apiUrl = getenv('GLE_API_URL') ?: '';
$username = getenv('GLE_USERNAME') ?: '';
$password = getenv('GLE_PASSWORD') ?: '';
$oauthClient = getenv('GLE_OAUTH_CLIENT') ?: '';
$oauthSecret = getenv('GLE_OAUTH_SECRET') ?: '';

if (empty($apiUrl) || empty($username) || empty($password) || empty($oauthClient) || empty($oauthSecret)) {
    die("Error: Missing required configuration. Set GLE_* environment variables.\n");
}

try {
    echo "Connecting to GlobalLink...\n";

    $client = new GlobalLinkClient(
        $apiUrl,
        $username,
        $password,
        $oauthClient,
        $oauthSecret
    );

    echo "Connected successfully!\n\n";

    // Get the first project
    $projectsResponse = $client->getProjects();
    if (!$projectsResponse->isSuccess() || empty($projectsResponse->projects)) {
        die("Error: No projects available.\n");
    }

    $project = $projectsResponse->projects[0];
    echo "Using project: {$project->name} (ID: {$project->projectId})\n\n";

    // === Poll for processed targets ===
    echo "=== Polling for Processed Targets ===\n";

    $request = new GetTargetsRequest(
        targetStatus: 'PROCESSED',
        projectIds: [$project->projectId],
        pageSize: 20
    );

    $targetsResponse = $client->getTargets($request);
    if (!$targetsResponse->isSuccess()) {
        die("Failed to get targets: {$targetsResponse->errorMessage}\n");
    }
    $targets = $targetsResponse->targets;

    if (empty($targets)) {
        echo "No processed targets found.\n";
        echo "Run the submissions example first to create some targets.\n\n";
    } else {
        echo "Found " . count($targets) . " processed target(s)\n\n";

        foreach ($targets as $target) {
            echo "Target ID: {$target->targetId}\n";
            echo "  Submission ID: {$target->submissionId}\n";
            echo "  Document: {$target->documentName}\n";
            echo "  Source: {$target->sourceLanguage}\n";
            echo "  Target: {$target->targetLanguage}\n";
            echo "  Status: {$target->status}\n";
            echo "  Current Phase: {$target->currentPhase}\n";

            if ($target->dateCompleted !== null) {
                $completedDate = date('Y-m-d H:i:s', (int) ($target->dateCompleted / 1000));
                echo "  Completed: {$completedDate}\n";
            }

            echo "\n";
        }

        // === Download a target ===
        if (!empty($targets)) {
            $firstTarget = $targets[0];
            echo "=== Downloading Target {$firstTarget->targetId} ===\n";

            $download = $client->downloadTargetDeliverable(
                $firstTarget->submissionId,
                $firstTarget->targetId
            );

            if (!$download->isSuccess()) {
                echo "Download/confirm failed: {$download->errorMessage}\n";
            } else {
                $content = $download->content;
                $filename = "downloaded_{$firstTarget->targetLanguage}_{$firstTarget->documentName}";
                // In production, you would save this to a file:
                // file_put_contents($filename, $content);

                echo "Downloaded " . strlen($content) . " bytes\n";
                echo "Would save to: {$filename}\n\n";

                // === Confirm delivery ===
                echo "=== Confirming Delivery ===\n";
                $confirmResponse = $client->confirmTargetDelivery(
                    $firstTarget->submissionId,
                    [$firstTarget->targetId]
                );

                if ($confirmResponse->isSuccess()) {
                    echo "Successfully delivered: " . count($confirmResponse->successfullyDeliveredTargets) . "\n";
                    echo "Failed to deliver: " . count($confirmResponse->failedToDeliverTargets) . "\n";
                } else {
                    echo "Confirm failed: {$confirmResponse->errorMessage}\n";
                }
            }
        }
    }

    // === Pagination Example ===
    echo "\n=== Pagination Example ===\n";
    echo "Note: pageSize must be between 20 and 200 (API requirement)\n\n";

    $pageNumber = 0;
    $pageSize = 20; // Minimum allowed page size
    $totalFetched = 0;
    $maxPages = 3; // Limit for demo purposes

    while ($pageNumber < $maxPages) {
        $paginatedRequest = new GetTargetsRequest(
            targetStatus: 'PROCESSED',
            projectIds: [$project->projectId],
            pageSize: $pageSize,
            pageNumber: $pageNumber
        );

        $pageTargetsResponse = $client->getTargets($paginatedRequest);
        if (!$pageTargetsResponse->isSuccess()) {
            echo "Page {$pageNumber}: error {$pageTargetsResponse->errorMessage}\n";
            break;
        }
        $pageTargets = $pageTargetsResponse->targets;
        $pageCount = count($pageTargets);
        $totalFetched += $pageCount;

        echo "Page {$pageNumber}: Retrieved {$pageCount} targets\n";

        if ($pageCount < $pageSize) {
            // No more pages available
            break;
        }
        $pageNumber++;
    }
    echo "Total fetched across {$pageNumber} page(s): {$totalFetched}\n";

    // === Poll for cancelled targets ===
    echo "\n=== Checking for Cancelled Targets ===\n";

    $cancelledRequest = new GetTargetsRequest(
        targetStatus: 'CANCELLED',
        projectIds: [$project->projectId],
        pageSize: 20 // Use minimum valid page size
    );

    $cancelledResponse = $client->getTargets($cancelledRequest);
    if (!$cancelledResponse->isSuccess()) {
        echo "Failed to load cancelled targets: {$cancelledResponse->errorMessage}\n";
    } else {
        echo "Found " . count($cancelledResponse->targets) . " cancelled target(s)\n";
    }

    // === Download all deliverables for a submission ===
    if (!empty($targets)) {
        $submissionId = $targets[0]->submissionId;

        echo "\n=== Bulk Download Options ===\n";
        echo "For submission ID: {$submissionId}\n\n";

        echo "Available download methods:\n";
        echo "1. downloadSubmissionDeliverables(\$submissionId)\n";
        echo "   - Downloads all deliverables with source and reference files\n";
        echo "   - Optional: includeManifest=true to include manifest file\n\n";

        echo "2. downloadSubmissionOnlyDeliverables(\$submissionId)\n";
        echo "   - Downloads only translated deliverables (no source/reference)\n";
        echo "   - Smaller download size\n\n";

        echo "3. downloadSubmissionDeliverablesByLanguages(\$submissionId, ['de-DE', 'fr-FR'])\n";
        echo "   - Downloads deliverables for specific target languages only\n\n";

        echo "4. downloadSubmissionDeliverablesByTargetIds(\$submissionId, [{$targets[0]->targetId}])\n";
        echo "   - Downloads specific targets by their IDs\n\n";

        // Example bulk download (commented out to avoid large downloads):
        /*
        echo "Downloading all deliverables...\n";
        $zipDownload = $client->downloadSubmissionDeliverables($submissionId, includeManifest: true);
        $zipContent = $zipDownload->content;

        // Note: The API may return direct ZIP content or a JSON response
        // with a downloadId for async downloads (for large submissions)
        if (!$zipDownload->isSuccess()) {
            echo "Bulk download failed: {$zipDownload->errorMessage}\n";
        } elseif (str_starts_with($zipContent, 'PK')) {
            // Direct ZIP content
            file_put_contents("deliverables_{$submissionId}.zip", $zipContent);
            echo "Saved to deliverables_{$submissionId}.zip\n";
        } else {
            // JSON response with downloadId for async download
            $data = json_decode($zipContent, true);
            if (isset($data['downloadId'])) {
                echo "Large download queued. Download ID: {$data['downloadId']}\n";
                // Poll for completion and download via separate endpoint
            }
        }
        */
    }

    echo "\nTarget workflow complete!\n";

} catch (GlobalLinkException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
