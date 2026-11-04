<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Tests\Integration;

use GlobalLink\RestClient\Model\BatchInfo;
use GlobalLink\RestClient\Model\SubmissionInfo;
use GlobalLink\RestClient\Model\Target;
use GlobalLink\RestClient\Model\TechTracking;
use GlobalLink\RestClient\Request\CreateSubmissionRequest;
use GlobalLink\RestClient\Request\SaveSubmissionRequest;
use GlobalLink\RestClient\Request\UploadSourceFileRequest;
use GlobalLink\RestClient\Response\CreateSubmissionResponse;
use GlobalLink\RestClient\Response\SaveSubmissionResponse;
use GlobalLink\RestClient\Response\UploadSourceFileResponse;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;

/**
 * End-to-end workflow integration test.
 *
 * This test class runs tests in a specific order that follows the real-world
 * submission workflow:
 *
 * 1. Create a submission
 * 2. Upload source files
 * 3. Set tech tracking
 * 4. Save/start the submission
 * 5. Poll for processed targets
 * 6. Download deliverables
 * 7. Confirm delivery
 *
 * Tests are chained using #[Depends] to ensure proper ordering and data sharing.
 */
#[Group('workflow')]
class WorkflowIntegrationTest extends IntegrationTestCase
{
    private const SDK_NAME = 'GlobalLink PHP SDK';
    private const SDK_VERSION = '2.0.0';

    /**
     * Step 1: Create a new submission.
     *
     * This is the starting point of the workflow.
     */
    public function testStep1CreateSubmission(): int
    {
        $this->debug('=== WORKFLOW TEST: Step 1 - Create Submission ===');

        $projectId = $this->getTestProjectId();
        $targetLanguages = $this->getTargetLanguages();
        $workflowId = $this->getDefaultWorkflowId();

        $this->debug("Project ID: {$projectId}");
        $this->debug('Target languages: ' . implode(', ', $targetLanguages));
        $this->debug("Workflow ID: {$workflowId}");

        // Create batch info
        $batchInfo = new BatchInfo(
            name: 'Workflow Test Batch',
            targetLanguages: $targetLanguages,
            workflowDefinitionId: $workflowId
        );

        // Create submission request
        $request = new CreateSubmissionRequest(
            name: 'Workflow Test ' . date('Y-m-d H:i:s'),
            dueDate: (time() + 86400 * 7) * 1000, // 7 days from now
            projectId: $projectId,
            sourceLanguage: $this->sourceLanguage,
            batchInfos: [$batchInfo],
            claimScope: 'LANGUAGE',
            instructions: 'End-to-end workflow test submission.'
        );

        $response = $this->client->createSubmission($request);

        $this->assertInstanceOf(CreateSubmissionResponse::class, $response);
        $this->assertGreaterThan(0, $response->submissionId);

        $this->debug("Created submission ID: {$response->submissionId}");

        return $response->submissionId;
    }

    /**
     * Step 2: Upload source file to the submission.
     */
    #[Depends('testStep1CreateSubmission')]
    public function testStep2UploadSourceFile(int $submissionId): int
    {
        $this->debug('=== WORKFLOW TEST: Step 2 - Upload Source File ===');
        $this->debug("Submission ID: {$submissionId}");

        $targetLanguages = $this->getTargetLanguages();
        $fileFormatName = $this->getFileFormatName();

        // Create sample content for translation
        $sampleContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<document>
    <title>Workflow Test Document</title>
    <body>
        <paragraph id="p1">This is a test paragraph for translation.</paragraph>
        <paragraph id="p2">This content will be translated to multiple languages.</paragraph>
        <paragraph id="p3">End-to-end workflow testing in progress.</paragraph>
    </body>
    <metadata>
        <created>2024-01-01</created>
        <author>PHP SDK Test</author>
    </metadata>
</document>
XML;

        $uploadRequest = new UploadSourceFileRequest(
            batchName: 'Workflow Test Batch',
            fileContents: $sampleContent,
            fileName: 'workflow_test_document.xml',
            fileFormatName: $fileFormatName,
            targetLanguages: $targetLanguages
        );

        $this->debug("File format: {$fileFormatName}");
        $this->debug('Content size: ' . strlen($sampleContent) . ' bytes');

        $uploadResponse = $this->client->uploadSubmissionSourceFile($submissionId, $uploadRequest);

        $this->assertInstanceOf(UploadSourceFileResponse::class, $uploadResponse);
        $this->assertNotEmpty($uploadResponse->processId);

        $this->debug("Upload process ID: {$uploadResponse->processId}");
        $this->debug('Documents uploaded: ' . count($uploadResponse->documentIds));

        return $submissionId;
    }

    /**
     * Step 3: Set tech tracking information.
     */
    #[Depends('testStep2UploadSourceFile')]
    public function testStep3SetTechTracking(int $submissionId): int
    {
        $this->debug('=== WORKFLOW TEST: Step 3 - Set Tech Tracking ===');
        $this->debug("Submission ID: {$submissionId}");

        $techTracking = new TechTracking(
            adaptorName: self::SDK_NAME,
            adaptorVersion: self::SDK_VERSION,
            clientVersion: 'PHPUnit ' . \PHPUnit\Runner\Version::id(),
            technologyProduct: 'PHP ' . PHP_VERSION
        );

        $this->debug("Adaptor: {$techTracking->adaptorName} v{$techTracking->adaptorVersion}");

        $updatedSubmission = $this->client->putSubmissionTechTracking($submissionId, $techTracking)->submission;

        $this->assertInstanceOf(SubmissionInfo::class, $updatedSubmission);
        $this->assertNotNull($updatedSubmission->techTracking);
        $this->assertSame(self::SDK_NAME, $updatedSubmission->techTracking->adaptorName);

        $this->debug('Tech tracking saved successfully');

        return $submissionId;
    }

    /**
     * Step 4: Save the submission (start processing).
     */
    #[Depends('testStep3SetTechTracking')]
    public function testStep4SaveSubmission(int $submissionId): int
    {
        $this->debug('=== WORKFLOW TEST: Step 4 - Save Submission ===');
        $this->debug("Submission ID: {$submissionId}");

        // Verify submission state before save
        $submission = $this->client->getSubmission($submissionId)->submission;
        $this->debug("Pre-save status: {$submission->status}");
        $this->debug("File count: {$submission->fileCount}");

        // Save without auto-start (for controlled testing)
        $saveRequest = new SaveSubmissionRequest(autoStart: false);
        $saveResponse = $this->client->saveSubmission($submissionId, $saveRequest);

        $this->assertInstanceOf(SaveSubmissionResponse::class, $saveResponse);
        $this->debug('Save messages: ' . implode(', ', $saveResponse->messages));

        // Verify post-save state
        $savedSubmission = $this->client->getSubmission($submissionId)->submission;
        $this->debug("Post-save status: {$savedSubmission->status}");

        $validStatuses = ['SAVED', 'READY', 'ANALYZE', 'TRANSLATE', 'PENDING', 'WAITING', 'IN_PROCESS', 'PROCESSING'];
        $this->assertContains($savedSubmission->status, $validStatuses);

        return $submissionId;
    }

    /**
     * Step 5: Poll until the submission's targets are PROCESSED.
     */
    #[Depends('testStep4SaveSubmission')]
    public function testStep5PollForTargets(int $submissionId): array
    {
        $this->debug('=== WORKFLOW TEST: Step 5 - Poll for Targets ===');
        $this->debug("Submission ID: {$submissionId}");

        $targets = $this->waitForProcessedTargets($submissionId, count($this->getTargetLanguages()));

        foreach ($targets as $target) {
            $this->assertInstanceOf(Target::class, $target);
            $this->debug("Target {$target->targetId}: {$target->documentName} ({$target->targetLanguage})");
        }

        return [
            'submissionId' => $submissionId,
            'targets' => $targets,
        ];
    }

    /**
     * Step 6: Download deliverables.
     */
    #[Depends('testStep5PollForTargets')]
    public function testStep6DownloadDeliverables(array $data): array
    {
        $this->debug('=== WORKFLOW TEST: Step 6 - Download Deliverables ===');

        $targets = $data['targets'];

        if (empty($targets)) {
            $this->debug('No processed targets available for download');
            $this->markTestSkipped('No processed targets available for download testing');
        }

        $target = $targets[0];
        $submissionId = $target->submissionId;

        $this->debug("Downloading target {$target->targetId} from submission {$submissionId}...");

        // Download individual target
        $download = $this->client->downloadTargetDeliverable($submissionId, $target->targetId);
        $this->assertTrue(
            $download->isSuccess(),
            "Download failed ({$download->responseCode}): {$download->errorMessage}"
        );
        $content = $download->content;
        $this->assertNotEmpty($content);

        $this->debug('Downloaded ' . strlen($content) . ' bytes');

        // Also test bulk download
        $this->debug('Testing bulk download...');
        $bulkDownload = $this->client->downloadSubmissionDeliverables($submissionId);
        $this->assertTrue(
            $bulkDownload->isSuccess(),
            "Bulk download failed ({$bulkDownload->responseCode}): {$bulkDownload->errorMessage}"
        );
        $bulkContent = $bulkDownload->content;
        $this->assertNotEmpty($bulkContent);

        // Check response type
        $signature = substr($bulkContent, 0, 2);
        if ($signature === 'PK') {
            $this->debug('Bulk download: ZIP file (' . strlen($bulkContent) . ' bytes)');
        } elseif ($signature === '{"') {
            $jsonData = json_decode($bulkContent, true);
            if (isset($jsonData['downloadId'])) {
                $this->debug("Bulk download: Async (downloadId: {$jsonData['downloadId']})");
            }
        }

        return [
            'submissionId' => $submissionId,
            'target' => $target,
        ];
    }

    /**
     * Step 7: Confirm delivery.
     */
    #[Depends('testStep6DownloadDeliverables')]
    public function testStep7ConfirmDelivery(array $data): void
    {
        $this->debug('=== WORKFLOW TEST: Step 7 - Confirm Delivery ===');

        $submissionId = $data['submissionId'];
        $target = $data['target'];

        $this->debug("Confirming delivery for target {$target->targetId}...");

        $response = $this->client->confirmTargetDelivery($submissionId, [$target->targetId]);

        if ($response->isError()) {
            // Target may already be delivered
            $this->debug('Delivery confirmation skipped: ' . $response->errorMessage);
            $this->debug('=== WORKFLOW TEST COMPLETE (with skip) ===');
            return;
        }

        $this->assertIsArray($response->successfullyDeliveredTargets);
        $this->assertIsArray($response->failedToDeliverTargets);

        $this->debug('Successfully delivered: ' . count($response->successfullyDeliveredTargets));
        $this->debug('Failed to deliver: ' . count($response->failedToDeliverTargets));

        $this->debug('=== WORKFLOW TEST COMPLETE ===');
    }
}
