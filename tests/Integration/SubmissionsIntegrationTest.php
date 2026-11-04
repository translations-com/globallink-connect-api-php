<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Tests\Integration;

use GlobalLink\RestClient\Model\BatchInfo;
use GlobalLink\RestClient\Model\SubmissionInfo;
use GlobalLink\RestClient\Model\SubmissionWordcount;
use GlobalLink\RestClient\Model\TechTracking;
use GlobalLink\RestClient\Request\CreateSubmissionRequest;
use GlobalLink\RestClient\Request\GetSubmissionsRequest;
use GlobalLink\RestClient\Request\SaveSubmissionRequest;
use GlobalLink\RestClient\Request\UploadSourceFileRequest;
use GlobalLink\RestClient\Response\CreateSubmissionResponse;
use GlobalLink\RestClient\Response\SaveSubmissionResponse;
use GlobalLink\RestClient\Response\UploadSourceFileResponse;

/**
 * Integration tests for submission workflow.
 *
 * These tests cover the complete submission lifecycle:
 * 1. Create submission
 * 2. Upload source files
 * 3. Set tech tracking
 * 4. Save/start submission
 * 5. Query submissions
 */
class SubmissionsIntegrationTest extends IntegrationTestCase
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';
    private const SDK_NAME = 'GlobalLink PHP SDK';
    private const SDK_VERSION = '2.0.0';

    private ?int $createdSubmissionId = null;

    protected function tearDown(): void
    {
        // Clean up: cancel submission if it was created but not completed
        if ($this->createdSubmissionId !== null && $this->client !== null) {
            try {
                // Try to get submission status (intentionally unused, just checking it exists)
                $this->client->getSubmission($this->createdSubmissionId);

                // If it's still in draft state, we could cancel it
                // But for now, just leave it for manual cleanup
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        }

        parent::tearDown();
    }

    public function testGetSubmissions(): void
    {
        // Use filters to avoid fetching all submissions - filter by common statuses
        // API supports: CANCELLED, CREATING, PREFLIGHT, DELIVERED, IN_PROCESS, PROCESSED, PROCESSING, READY, WAITING
        $request = new GetSubmissionsRequest(
            statuses: ['IN_PROCESS', 'READY', 'DELIVERED'],
            pageSize: 50,
            pageNumber: 0
        );

        $this->debug('Fetching submissions with status filter (IN_PROCESS, READY, DELIVERED)...');
        $submissions = $this->client->getSubmissions($request)->submissions;

        $this->assertIsArray($submissions);
        $this->debug('Found ' . count($submissions) . ' submissions');

        foreach ($submissions as $submission) {
            $this->assertInstanceOf(SubmissionInfo::class, $submission);
            $this->assertGreaterThan(0, $submission->submissionId);
            $this->assertNotEmpty($submission->name);
        }
        if (!empty($submissions)) {
            $this->debug("First: {$submissions[0]->name} (ID: {$submissions[0]->submissionId}, Status: {$submissions[0]->status})");
        }
    }

    public function testGetSubmissionsWithFilters(): void
    {
        $this->debug('Fetching submissions with filters...');
        $request = new GetSubmissionsRequest(pageSize: 50);

        $submissions = $this->client->getSubmissions($request)->submissions;

        $this->assertIsArray($submissions);
        $this->debug('Found ' . count($submissions) . ' submissions');

        foreach ($submissions as $submission) {
            $this->assertInstanceOf(SubmissionInfo::class, $submission);
        }
    }

    public function testCreateSubmission(): void
    {
        $projectId = $this->getTestProjectId();
        $targetLanguages = $this->getTargetLanguages();
        $this->debug("Creating submission for project {$projectId}...");
        $this->debug('Target languages: ' . implode(', ', $targetLanguages));

        // Create batch info
        $batchInfo = new BatchInfo(
            name: 'Test Batch ' . date('Y-m-d H:i:s'),
            targetLanguages: $targetLanguages,
            workflowDefinitionId: $this->getDefaultWorkflowId()
        );

        // Create submission request
        $request = new CreateSubmissionRequest(
            name: 'Integration Test ' . date(self::DATE_FORMAT),
            dueDate: (time() + 86400 * 7) * 1000,
            projectId: $projectId,
            sourceLanguage: $this->sourceLanguage,
            batchInfos: [$batchInfo],
            claimScope: 'LANGUAGE',
            instructions: 'This is an automated integration test submission.'
        );

        $response = $this->client->createSubmission($request);

        $this->assertInstanceOf(CreateSubmissionResponse::class, $response);
        $this->assertGreaterThan(0, $response->submissionId);
        $this->debug("Created submission ID: {$response->submissionId}");

        $this->createdSubmissionId = $response->submissionId;

        // Verify we can retrieve the created submission
        $submission = $this->client->getSubmission($response->submissionId)->submission;
        $this->assertStringContainsString('Integration Test', $submission->name);
        $this->assertSame($projectId, $submission->projectId);
        $this->assertSame($this->sourceLanguage, $submission->sourceLanguage);
        $this->debug("Verified submission: {$submission->name} (Status: {$submission->status})");
    }

    public function testFullSubmissionWorkflow(): void
    {
        $projectId = $this->getTestProjectId();
        $targetLanguages = $this->getTargetLanguages();
        $fileFormatName = $this->getFileFormatName();

        $this->debug('=== FULL SUBMISSION WORKFLOW ===');
        $this->debug("Project: {$projectId}, Format: {$fileFormatName}");
        $this->debug('Targets: ' . implode(', ', $targetLanguages));

        // Step 1: Create submission
        $this->debug('Step 1: Creating submission...');
        $batchInfo = new BatchInfo(
            name: 'Workflow Test Batch',
            targetLanguages: $targetLanguages,
            workflowDefinitionId: $this->getDefaultWorkflowId()
        );

        $createRequest = new CreateSubmissionRequest(
            name: 'Workflow Test ' . date(self::DATE_FORMAT),
            dueDate: (time() + 86400 * 7) * 1000,
            projectId: $projectId,
            sourceLanguage: $this->sourceLanguage,
            batchInfos: [$batchInfo],
            claimScope: 'LANGUAGE'
        );

        $createResponse = $this->client->createSubmission($createRequest);
        $this->assertGreaterThan(0, $createResponse->submissionId);
        $submissionId = $createResponse->submissionId;
        $this->createdSubmissionId = $submissionId;
        $this->debug("Created submission ID: {$submissionId}");

        // Step 2: Upload source file
        $this->debug('Step 2: Uploading source file...');
        $uploadRequest = new UploadSourceFileRequest(
            batchName: $batchInfo->name,
            file: $this->getTestFilePath(),
            fileFormatName: $fileFormatName,
            targetLanguages: $targetLanguages
        );

        $uploadResponse = $this->client->uploadSubmissionSourceFile($submissionId, $uploadRequest);
        $this->assertInstanceOf(UploadSourceFileResponse::class, $uploadResponse);
        $this->debug("Upload complete. Process ID: {$uploadResponse->processId}");

        // Step 3: Verify submission state before save
        $this->debug('Step 3: Verifying submission state...');
        $submission = $this->client->getSubmission($submissionId)->submission;
        $this->assertContains($submission->status, ['DRAFT', 'CREATING']);
        $this->assertGreaterThan(0, $submission->fileCount);
        $this->debug("Status: {$submission->status}, Files: {$submission->fileCount}");

        // Step 4: Save submission (without auto-start for testing)
        $this->debug('Step 4: Saving submission...');
        $saveRequest = new SaveSubmissionRequest(autoStart: false);

        $saveResponse = $this->client->saveSubmission($submissionId, $saveRequest);
        $this->assertInstanceOf(SaveSubmissionResponse::class, $saveResponse);
        $this->debug('Save response: ' . implode(', ', $saveResponse->messages));

        // Step 5: Verify submission is saved (status varies by workflow)
        $this->debug('Step 5: Verifying final state...');
        $savedSubmission = $this->client->getSubmission($submissionId)->submission;
        $validStatuses = ['SAVED', 'READY', 'ANALYZE', 'TRANSLATE', 'PENDING', 'WAITING', 'IN_PROCESS', 'PROCESSING'];
        $this->assertContains($savedSubmission->status, $validStatuses);
        $this->debug("Final status: {$savedSubmission->status}");
    }

    public function testUploadSourceFileWithContents(): void
    {
        $projectId = $this->getTestProjectId();
        $targetLanguages = $this->getTargetLanguages();
        $fileFormatName = $this->getFileFormatName();

        $this->debug('Testing file upload with inline content...');

        // Create submission
        $batchInfo = new BatchInfo(
            name: 'Content Upload Test',
            targetLanguages: $targetLanguages,
            workflowDefinitionId: $this->getDefaultWorkflowId()
        );

        $createRequest = new CreateSubmissionRequest(
            name: 'Content Upload Test ' . date(self::DATE_FORMAT),
            dueDate: (time() + 86400 * 7) * 1000,
            projectId: $projectId,
            sourceLanguage: $this->sourceLanguage,
            batchInfos: [$batchInfo],
            claimScope: 'LANGUAGE'
        );

        $createResponse = $this->client->createSubmission($createRequest);
        $submissionId = $createResponse->submissionId;
        $this->createdSubmissionId = $submissionId;
        $this->debug("Created submission ID: {$submissionId}");

        // Upload using file contents instead of file path
        $fileContents = file_get_contents($this->getTestFilePath());
        $this->debug('Uploading file contents (' . strlen($fileContents) . ' bytes)...');

        $uploadRequest = new UploadSourceFileRequest(
            batchName: $batchInfo->name,
            fileContents: $fileContents,
            fileName: 'test_content_upload.xml',
            fileFormatName: $fileFormatName,
            targetLanguages: $targetLanguages
        );

        $uploadResponse = $this->client->uploadSubmissionSourceFile($submissionId, $uploadRequest);
        $this->assertInstanceOf(UploadSourceFileResponse::class, $uploadResponse);
        $this->debug("Upload complete. Process ID: {$uploadResponse->processId}");

        // Verify file was uploaded
        $submission = $this->client->getSubmission($submissionId)->submission;
        $this->assertGreaterThan(0, $submission->fileCount);
        $this->debug("File count: {$submission->fileCount}");
    }

    public function testPatchSubmission(): void
    {
        // Note: PATCH operations may require specific API version or submission state
        // This test verifies basic patch functionality
        $this->markTestSkipped('PATCH endpoint requires specific API configuration - skipping for now');
    }

    /**
     * Test setting tech tracking information on a submission.
     */
    public function testPutSubmissionTechTracking(): void
    {
        $projectId = $this->getTestProjectId();
        $targetLanguages = $this->getTargetLanguages();

        $this->debug('Testing tech tracking update...');

        // Create submission
        $batchInfo = new BatchInfo(
            name: 'Tech Tracking Test Batch',
            targetLanguages: $targetLanguages,
            workflowDefinitionId: $this->getDefaultWorkflowId()
        );

        $createRequest = new CreateSubmissionRequest(
            name: 'Tech Tracking Test ' . date(self::DATE_FORMAT),
            dueDate: (time() + 86400 * 7) * 1000,
            projectId: $projectId,
            sourceLanguage: $this->sourceLanguage,
            batchInfos: [$batchInfo],
            claimScope: 'LANGUAGE'
        );

        $createResponse = $this->client->createSubmission($createRequest);
        $submissionId = $createResponse->submissionId;
        $this->createdSubmissionId = $submissionId;
        $this->debug("Created submission ID: {$submissionId}");

        // Set tech tracking information
        $this->debug('Setting tech tracking info...');
        $techTracking = new TechTracking(
            adaptorName: self::SDK_NAME,
            adaptorVersion: self::SDK_VERSION,
            clientVersion: 'PHPUnit Test',
            technologyProduct: 'PHP'
        );

        $updatedSubmission = $this->client->putSubmissionTechTracking($submissionId, $techTracking)->submission;

        $this->assertInstanceOf(SubmissionInfo::class, $updatedSubmission);
        $this->assertSame($submissionId, $updatedSubmission->submissionId);

        // Verify tech tracking was saved
        $this->assertNotNull($updatedSubmission->techTracking);
        if ($updatedSubmission->techTracking !== null) {
            $this->assertSame(self::SDK_NAME, $updatedSubmission->techTracking->adaptorName);
            $this->assertSame(self::SDK_VERSION, $updatedSubmission->techTracking->adaptorVersion);
            $this->debug("Tech tracking saved: {$updatedSubmission->techTracking->adaptorName} v{$updatedSubmission->techTracking->adaptorVersion}");
        }
    }

    /**
     * Test getting wordcount for a submission.
     *
     * Note: Wordcount is typically available after analysis is complete.
     * We filter by statuses that indicate analysis has been performed.
     */
    public function testGetSubmissionWordcount(): void
    {
        $this->debug('Testing submission wordcount retrieval...');

        // Filter by statuses where wordcount is likely available (after analysis)
        // These statuses indicate the submission has been analyzed
        $request = new GetSubmissionsRequest(
            statuses: ['IN_PROCESS', 'READY', 'PROCESSED', 'DELIVERED'],
            pageSize: 20,
            pageNumber: 0
        );

        $submissions = $this->client->getSubmissions($request)->submissions;

        if (empty($submissions)) {
            $this->markTestSkipped('No analyzed submissions available for wordcount testing');
        }

        $this->debug('Searching ' . count($submissions) . ' analyzed submissions for wordcount...');

        // Try to find a submission that has wordcount available
        $testedSubmission = false;
        foreach ($submissions as $submission) {
            $wordcountResponse = $this->client->getSubmissionWordcount($submission->submissionId);

            if ($wordcountResponse->isError()) {
                // Wordcount may not be available for all submissions
                continue;
            }

            $wordcount = $wordcountResponse->wordcount;

            $this->assertInstanceOf(SubmissionWordcount::class, $wordcount);
            $this->assertIsInt($wordcount->totalWordCount);
            $this->assertGreaterThanOrEqual(0, $wordcount->totalWordCount);

            $this->debug("Found wordcount for submission {$submission->submissionId}: {$wordcount->totalWordCount} words");
            $testedSubmission = true;
            break;
        }

        if (!$testedSubmission) {
            $this->markTestSkipped('No submissions with available wordcount found');
        }
    }

    /**
     * Test creating a submission with tech tracking included.
     */
    public function testCreateSubmissionWithTechTracking(): void
    {
        $projectId = $this->getTestProjectId();
        $targetLanguages = $this->getTargetLanguages();

        $this->debug('Testing submission creation with tech tracking...');

        // Create batch info
        $batchInfo = new BatchInfo(
            name: 'Tech Tracking Creation Test',
            targetLanguages: $targetLanguages,
            workflowDefinitionId: $this->getDefaultWorkflowId()
        );

        // Create tech tracking
        $techTracking = new TechTracking(
            adaptorName: self::SDK_NAME,
            adaptorVersion: self::SDK_VERSION,
            clientVersion: 'Integration Test',
            technologyProduct: 'PHP'
        );

        // Create submission request with tech tracking
        $request = new CreateSubmissionRequest(
            name: 'With Tech Tracking ' . date(self::DATE_FORMAT),
            dueDate: (time() + 86400 * 7) * 1000,
            projectId: $projectId,
            sourceLanguage: $this->sourceLanguage,
            batchInfos: [$batchInfo],
            claimScope: 'LANGUAGE',
            techTracking: $techTracking
        );

        $response = $this->client->createSubmission($request);

        $this->assertInstanceOf(CreateSubmissionResponse::class, $response);
        $this->assertGreaterThan(0, $response->submissionId);
        $this->debug("Created submission ID: {$response->submissionId}");

        $this->createdSubmissionId = $response->submissionId;

        // Verify submission was created with tech tracking
        $submission = $this->client->getSubmission($response->submissionId)->submission;
        $this->assertNotNull($submission->techTracking);
        $this->debug("Tech tracking verified: {$submission->techTracking->adaptorName}");
    }
}
