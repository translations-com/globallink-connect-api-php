<?php

declare(strict_types=1);

/**
 * Example: Working with Submissions
 *
 * This example demonstrates how to:
 * - Create a new submission
 * - Upload source files
 * - Upload reference files
 * - Add tech tracking
 * - Save and start the submission
 * - Get submission wordcount
 * - Cancel a submission (optional)
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use TransPerfect\GlobalLink\GlobalLinkClient;
use TransPerfect\GlobalLink\Request\CancelSubmissionRequest;
use TransPerfect\GlobalLink\Request\CreateSubmissionRequest;
use TransPerfect\GlobalLink\Request\GetSubmissionsRequest;
use TransPerfect\GlobalLink\Request\SaveSubmissionRequest;
use TransPerfect\GlobalLink\Request\UploadReferenceFileRequest;
use TransPerfect\GlobalLink\Request\UploadSourceFileRequest;
use TransPerfect\GlobalLink\Model\BatchInfo;
use TransPerfect\GlobalLink\Model\CreateSubmissionTargetLanguageInfo;
use TransPerfect\GlobalLink\Model\TechTracking;
use TransPerfect\GlobalLink\Exception\GlobalLinkException;

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

    // Get the first project for creating a submission
    $projects = $client->getProjects();
    if (empty($projects)) {
        die("Error: No projects available.\n");
    }

    $project = $projects[0];
    echo "Using project: {$project->name} (ID: {$project->projectId})\n\n";

    // Get project details for language directions
    $projectInfo = $client->getProject($project->projectId);
    $languageDirections = $projectInfo->projectLanguageDirections;

    if (empty($languageDirections)) {
        die("Error: No language directions configured for this project.\n");
    }

    // Get file formats
    $fileFormats = $client->getProjectFileFormats($project->projectId);
    $defaultFormat = null;
    foreach ($fileFormats as $format) {
        if ($format->default) {
            $defaultFormat = $format;
            break;
        }
    }

    // === List existing submissions ===
    // Use filters to narrow results - avoid fetching all submissions
    // Available statuses: CANCELLED, CREATING, PREFLIGHT, DELIVERED, IN_PROCESS, PROCESSED, PROCESSING, READY, WAITING
    echo "=== Existing Submissions ===\n";
    $submissionsRequest = new GetSubmissionsRequest(
        statuses: ['IN_PROCESS', 'READY', 'DELIVERED'],
        pageSize: 20, // Minimum allowed: 20, Maximum: 200, Default: 50
        pageNumber: 0
    );
    $submissions = $client->getSubmissions($submissionsRequest);

    if (empty($submissions)) {
        echo "No submissions found.\n";
    } else {
        foreach ($submissions as $submission) {
            echo "Submission: {$submission->name} (ID: {$submission->submissionId})\n";
            echo "  Status: {$submission->status}\n";
            echo "  Source: {$submission->sourceLanguage}\n";
            echo "  Targets: " . implode(', ', $submission->targetLanguages) . "\n\n";
        }
    }

    // === Create a new submission ===
    echo "=== Creating New Submission ===\n";

    // Build batch info with target languages
    $targetLanguages = [];
    $targetLanguageInfos = [];
    $count = 0;
    foreach ($languageDirections as $direction) {
        if ($count >= 2) {
            break;
        }
        $targetLanguageInfos[] = new CreateSubmissionTargetLanguageInfo($direction->targetLanguage);
        $targetLanguages[] = $direction->targetLanguage;
        $count++;
    }

    $batch = new BatchInfo(
        name: 'Main Batch',
        targetFormat: 'TXLF',
        targetLanguageInfos: $targetLanguageInfos
    );

    // Create submission request
    $createRequest = new CreateSubmissionRequest(
        name: 'PHP SDK Test - ' . date('Y-m-d H:i:s'),
        dueDate: (time() + 7 * 24 * 60 * 60) * 1000, // 7 days from now in milliseconds
        projectId: $project->projectId,
        sourceLanguage: $languageDirections[0]->sourceLanguage,
        batchInfos: [$batch],
        claimScope: 'LANGUAGE',
        instructions: 'This is a test submission created via the PHP SDK.'
    );

    echo "Creating submission: {$createRequest->name}\n";
    echo "Source language: {$createRequest->sourceLanguage}\n";
    echo "Target languages: " . implode(', ', $targetLanguages) . "\n";

    $createResponse = $client->createSubmission($createRequest);
    $submissionId = $createResponse->submissionId;
    echo "Created submission ID: {$submissionId}\n\n";

    // === Add tech tracking ===
    echo "=== Adding Tech Tracking ===\n";
    $techTracking = new TechTracking(
        adaptorName: 'PHP SDK Example',
        adaptorVersion: '1.0.0',
        clientVersion: PHP_VERSION,
        technologyProduct: 'PHP'
    );

    $client->putSubmissionTechTracking($submissionId, $techTracking);
    echo "Tech tracking added.\n\n";

    // === Upload a source file ===
    echo "=== Uploading Source File ===\n";

    // Create a sample file content
    $sampleContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<document>
    <title>Sample Document</title>
    <body>
        <paragraph>This is a sample paragraph to be translated.</paragraph>
        <paragraph>This is another paragraph with more content.</paragraph>
    </body>
</document>
XML;

    $uploadRequest = new UploadSourceFileRequest(
        batchName: 'Main Batch',
        fileContents: $sampleContent,
        fileName: 'sample-document.xml',
        fileFormatName: $defaultFormat?->name
    );

    if ($defaultFormat !== null) {
        echo "Using file format: {$defaultFormat->name}\n";
    }

    $uploadResponse = $client->uploadSubmissionSourceFile($submissionId, $uploadRequest);
    echo "Uploaded file. Process ID: {$uploadResponse->processId}\n";
    echo "Document IDs: " . count($uploadResponse->documentIds) . "\n\n";

    // === Upload a reference file ===
    echo "=== Uploading Reference File ===\n";

    // Reference files provide context for translators (e.g., glossaries, style guides)
    $referenceContent = <<<TXT
Translation Guidelines
======================

1. Use formal tone throughout
2. Keep technical terms in English
3. Target audience: Business professionals

Key Terms:
- Document -> Document (keep as-is)
- Paragraph -> Paragraph (keep as-is)
TXT;

    $refUploadRequest = new UploadReferenceFileRequest(
        fileContents: $referenceContent,
        fileName: 'translation-guidelines.txt'
    );

    $refUploadResponse = $client->uploadSubmissionReferenceFile($submissionId, $refUploadRequest);
    echo "Uploaded reference file. Process ID: {$refUploadResponse->processId}\n\n";

    // === Get submission details ===
    echo "=== Submission Details ===\n";
    $submission = $client->getSubmission($submissionId);
    echo "Name: {$submission->name}\n";
    echo "Status: {$submission->status}\n";
    echo "File count: {$submission->fileCount}\n";
    echo "Tech tracking:\n";
    if ($submission->techTracking !== null) {
        echo "  Adaptor: {$submission->techTracking->adaptorName} {$submission->techTracking->adaptorVersion}\n";
    }
    echo "\n";

    // === Save the submission (do not auto-start in this example) ===
    echo "=== Saving Submission ===\n";
    $saveRequest = new SaveSubmissionRequest(autoStart: false);
    $saveResponse = $client->saveSubmission($submissionId, $saveRequest);

    echo "Submission saved.\n";
    echo "Messages: " . implode(', ', $saveResponse->messages) . "\n\n";

    // Note: To start the submission, use autoStart: true or start it manually
    // $saveRequest = new SaveSubmissionRequest(autoStart: true);

    // === Patch Submission (modify individual fields) ===
    // patchSubmission() allows modifying specific submission fields using JSON Patch operations
    // Example: Update submission instructions
    /*
    $updatedSubmission = $client->patchSubmission(
        $submissionId,
        op: 'replace',
        path: '/instructions',
        value: 'Updated instructions via patch'
    );
    echo "Instructions updated: {$updatedSubmission->instructions}\n";
    */

    // === Get submission wordcount ===
    echo "=== Submission Wordcount ===\n";
    try {
        $wordcount = $client->getSubmissionWordcount($submissionId);
        echo "Total word count: {$wordcount->totalWordCount}\n";
        echo "Total segments: {$wordcount->totalSegments}\n";
        echo "Leverage breakdown:\n";
        echo "  - In-context match: {$wordcount->inContextMatch}\n";
        echo "  - 100% match: {$wordcount->match100}\n";
        echo "  - 95-99% match: {$wordcount->match95to99}\n";
        echo "  - 85-94% match: {$wordcount->match85to94}\n";
        echo "  - 75-84% match: {$wordcount->match75to84}\n";
        echo "  - No match: {$wordcount->noMatch}\n";
        echo "  - Repetitions: {$wordcount->repetitions}\n";
    } catch (GlobalLinkException $e) {
        // Wordcount may not be available for draft submissions
        echo "Wordcount not available: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // === Cancel submission (optional) ===
    // Uncomment to test cancellation. Only works for submissions not yet started.
    // Note: You can cancel specific targets or documents, or pass no parameters
    // to cancel the entire submission.
    /*
    echo "=== Cancelling Submission ===\n";
    // Cancel entire submission (no specific targets/documents)
    $cancelRequest = new CancelSubmissionRequest();
    // Or cancel specific targets: new CancelSubmissionRequest(targetIds: [123, 456])
    // Or cancel specific documents: new CancelSubmissionRequest(documentIds: [789])

    try {
        $cancelled = $client->cancelSubmission($submissionId, $cancelRequest);
        if ($cancelled) {
            echo "Submission cancelled successfully.\n";
        } else {
            echo "Submission could not be cancelled.\n";
        }
    } catch (GlobalLinkException $e) {
        echo "Cancel failed: " . $e->getMessage() . "\n";
    }
    */

    echo "\nSubmission workflow complete!\n";

} catch (GlobalLinkException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
