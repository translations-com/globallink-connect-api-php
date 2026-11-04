<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Tests\Integration;

use GlobalLink\RestClient\Model\Target;
use GlobalLink\RestClient\Request\GetSubmissionsRequest;
use GlobalLink\RestClient\Request\GetTargetsRequest;
use GlobalLink\RestClient\Response\ConfirmTargetDeliveredResponse;

/**
 * Integration tests for downloading completed/translated content.
 *
 * These tests cover:
 * 1. Polling for completed targets
 * 2. Downloading individual target deliverables
 * 3. Downloading submission deliverables (all, by language, by target ID)
 * 4. Marking targets as delivered
 *
 * Note: The download tests create their own submission and wait for its targets
 * to be PROCESSED (see GLE_TEST_PROCESSING_TIMEOUT). The test project's default
 * workflow should therefore complete automatically, e.g. a pseudo-translation workflow.
 *
 * @see https://onlinehelp.transperfect.com/GLE/RESTAPI/4-endpoint-reference.html
 */
class DownloadIntegrationTest extends IntegrationTestCase
{
    /**
     * Skip message when no processed targets are available.
     */
    private const NO_PROCESSED_TARGETS_MSG = 'No processed targets available for download testing';

    /**
     * PROCESSED targets of the submission created for this test class.
     *
     * @var list<Target>|null
     */
    private static ?array $processedTargets = null;

    /**
     * Test polling for completed (PROCESSED) targets.
     */
    public function testGetTargetsByStatusProcessed(): void
    {
        $this->debug('Polling for PROCESSED targets...');

        // Get processed targets (handles API variations internally)
        $targets = $this->getProcessedTargets();

        $this->assertIsArray($targets);
        $this->debug('Found ' . count($targets) . ' processed targets');

        // Verify each target has the expected structure
        foreach ($targets as $target) {
            $this->assertInstanceOf(Target::class, $target);
            $this->assertSame('PROCESSED', $target->status);
            $this->assertNotNull($target->targetId);
            $this->assertNotNull($target->submissionId);
            $this->assertNotNull($target->targetLanguage);
        }

        if (!empty($targets)) {
            $this->debug("First target: ID {$targets[0]->targetId}, Lang: {$targets[0]->targetLanguage}");
        }
    }

    /**
     * Test polling for cancelled targets.
     */
    public function testGetTargetsByStatusCancelled(): void
    {
        $this->debug('Polling for CANCELLED targets...');

        // Filter submissions by status to find those that might have cancelled targets
        // Use CANCELLED status to find submissions with cancelled content
        $submissionRequest = new GetSubmissionsRequest(
            statuses: ['CANCELLED', 'IN_PROCESS', 'DELIVERED'],
            pageSize: 20,
            pageNumber: 0
        );
        $submissions = $this->client->getSubmissions($submissionRequest)->submissions;

        if (empty($submissions)) {
            $this->markTestSkipped('No submissions available for cancelled targets testing');
        }

        $submissionIds = array_map(fn($s) => $s->submissionId, array_slice($submissions, 0, 10));
        $this->debug('Checking ' . count($submissionIds) . ' submissions for cancelled targets...');

        $request = new GetTargetsRequest(
            targetStatus: 'CANCELLED',
            submissionIds: $submissionIds,
            pageSize: 20 // Minimum allowed by API
        );

        $targetsResponse = $this->client->getTargets($request);

        if ($targetsResponse->isError()) {
            $this->markTestSkipped('Cancelled status polling not available: ' . $targetsResponse->errorMessage);
        }

        $targets = $targetsResponse->targets;

        $this->assertIsArray($targets);
        $this->debug('Found ' . count($targets) . ' cancelled targets');

        foreach ($targets as $target) {
            $this->assertInstanceOf(Target::class, $target);
            $this->assertSame('CANCELLED', $target->status);
        }
    }

    /**
     * Test downloading a single target deliverable.
     */
    public function testDownloadTargetDeliverable(): void
    {
        $this->debug('Testing single target download...');
        $targets = $this->getProcessedTargets();

        if (empty($targets)) {
            $this->markTestSkipped(self::NO_PROCESSED_TARGETS_MSG);
        }

        $target = $targets[0];
        $this->debug("Downloading target {$target->targetId} from submission {$target->submissionId}...");

        // Download the individual target deliverable
        $response = $this->client->downloadTargetDeliverable(
            $target->submissionId,
            $target->targetId
        );

        $this->assertTrue(
            $response->isSuccess(),
            "Download failed ({$response->responseCode}): {$response->errorMessage}"
        );
        $content = $response->content;
        $this->assertNotEmpty($content);
        $this->debug('Downloaded ' . strlen($content) . ' bytes');
    }

    /**
     * Test downloading all deliverables for a submission.
     *
     * Note: The API may return either:
     * - Direct ZIP content for synchronous downloads
     * - JSON with downloadId for asynchronous downloads (requires polling)
     */
    public function testDownloadSubmissionDeliverables(): void
    {
        $this->debug('Testing submission bulk download...');
        $targets = $this->getProcessedTargets();

        if (empty($targets)) {
            $this->markTestSkipped(self::NO_PROCESSED_TARGETS_MSG);
        }

        // Group targets by submission
        $submissionId = $targets[0]->submissionId;
        $this->debug("Downloading all deliverables for submission {$submissionId}...");

        // Download all deliverables with manifest
        $content = $this->client->downloadSubmissionDeliverables(
            $submissionId,
            downloadAllDeliverables: true,
            includeManifest: true
        )->content;

        // Verify we received content
        $this->assertIsString($content);
        $this->assertNotEmpty($content);
        $this->debug('Downloaded ' . strlen($content) . ' bytes');

        // Check response type - could be ZIP or JSON (async download with downloadId)
        if (strlen($content) >= 2) {
            $signature = substr($content, 0, 2);
            if ($signature === 'PK') {
                $this->debug('Response type: ZIP file (synchronous download)');
                $this->assertTrue(true, 'Received ZIP file content');
            } elseif ($signature === '{"') {
                $this->debug('Response type: JSON (async download)');
                $data = json_decode($content, true);
                $this->assertNotNull($data, 'Expected valid JSON response');
                if (isset($data['downloadId'])) {
                    $this->debug("Async download ID: {$data['downloadId']}");
                    $this->assertNotEmpty($data['downloadId'], 'Expected downloadId for async download');
                }
            }
        }
    }

    /**
     * Test downloading only deliverable files (no collaterals).
     */
    public function testDownloadSubmissionOnlyDeliverables(): void
    {
        $this->debug('Testing download only deliverables (no collaterals)...');
        $targets = $this->getProcessedTargets();

        if (empty($targets)) {
            $this->markTestSkipped(self::NO_PROCESSED_TARGETS_MSG);
        }

        $submissionId = $targets[0]->submissionId;
        $this->debug("Downloading only deliverables for submission {$submissionId}...");

        $content = $this->client->downloadSubmissionOnlyDeliverables(
            $submissionId,
            downloadOnlyDeliverables: true,
            includeManifest: false
        )->content;

        $this->assertIsString($content);
        $this->assertNotEmpty($content);
        $this->debug('Downloaded ' . strlen($content) . ' bytes');
    }

    /**
     * Test downloading deliverables by target language.
     */
    public function testDownloadSubmissionDeliverablesByLanguage(): void
    {
        $this->debug('Testing download by language...');
        $targets = $this->getProcessedTargets();

        if (empty($targets)) {
            $this->markTestSkipped(self::NO_PROCESSED_TARGETS_MSG);
        }

        $submissionId = $targets[0]->submissionId;
        $targetLanguage = $targets[0]->targetLanguage;
        $this->debug("Downloading deliverables for language {$targetLanguage} from submission {$submissionId}...");

        $content = $this->client->downloadSubmissionDeliverablesByLanguages(
            $submissionId,
            deliverableLanguages: [$targetLanguage],
            includeManifest: true
        )->content;

        $this->assertIsString($content);
        $this->assertNotEmpty($content);
        $this->debug('Downloaded ' . strlen($content) . ' bytes');
    }

    /**
     * Test downloading deliverables by specific target IDs.
     */
    public function testDownloadSubmissionDeliverablesByTargetIds(): void
    {
        $this->debug('Testing download by target IDs...');
        $targets = $this->getProcessedTargets();

        if (empty($targets)) {
            $this->markTestSkipped(self::NO_PROCESSED_TARGETS_MSG);
        }

        // Get targets from the same submission
        $submissionId = $targets[0]->submissionId;
        $targetIds = [];

        foreach ($targets as $target) {
            if ($target->submissionId === $submissionId) {
                $targetIds[] = $target->targetId;
                if (count($targetIds) >= 3) {
                    break;
                }
            }
        }

        $this->debug("Downloading " . count($targetIds) . " targets from submission {$submissionId}...");

        $content = $this->client->downloadSubmissionDeliverablesByTargetIds(
            $submissionId,
            deliverableTargetIds: $targetIds,
            includeManifest: true
        )->content;

        $this->assertIsString($content);
        $this->assertNotEmpty($content);
        $this->debug('Downloaded ' . strlen($content) . ' bytes');
    }

    /**
     * Test marking targets as delivered.
     *
     * Note: This test modifies target state and may affect production data.
     * The test uses processed targets and marks them as delivered.
     */
    public function testConfirmTargetDelivery(): void
    {
        $this->debug('Testing target delivery confirmation...');
        $processedTargets = $this->getProcessedTargets();

        if (empty($processedTargets)) {
            $this->markTestSkipped('No processed targets available for delivery confirmation testing');
        }

        // Mark a processed target as delivered
        $target = $processedTargets[0];
        $this->debug("Confirming delivery for target {$target->targetId}...");

        $response = $this->client->confirmTargetDelivery(
            $target->submissionId,
            [$target->targetId]
        );

        if ($response->isError()) {
            $this->markTestSkipped('Target delivery confirmation not available: ' . $response->errorMessage);
        }

        $this->assertInstanceOf(ConfirmTargetDeliveredResponse::class, $response);
        $this->assertIsArray($response->successfullyDeliveredTargets);
        $this->assertIsArray($response->failedToDeliverTargets);

        $this->debug('Success: ' . count($response->successfullyDeliveredTargets) . ', Failed: ' . count($response->failedToDeliverTargets));
    }

    /**
     * Test polling for targets by submission IDs.
     */
    public function testGetTargetsBySubmissionIds(): void
    {
        $this->debug('Testing target polling by submission IDs...');

        // Filter submissions by status to find those likely to have PROCESSED targets
        $submissionRequest = new GetSubmissionsRequest(
            statuses: ['IN_PROCESS', 'READY', 'PROCESSED', 'DELIVERED'],
            pageSize: 20, // Minimum allowed by API
            pageNumber: 0
        );
        $submissions = $this->client->getSubmissions($submissionRequest)->submissions;

        if (empty($submissions)) {
            $this->markTestSkipped('No submissions available for testing');
        }

        // Take first few submission IDs
        $submissionIds = [];
        foreach (array_slice($submissions, 0, 3) as $submission) {
            $submissionIds[] = $submission->submissionId;
        }

        $this->debug('Polling targets for ' . count($submissionIds) . ' submissions...');

        $request = new GetTargetsRequest(
            targetStatus: 'PROCESSED',
            submissionIds: $submissionIds,
            pageSize: 50
        );

        $targets = $this->client->getTargets($request)->targets;

        $this->assertIsArray($targets);
        $this->debug('Found ' . count($targets) . ' targets');

        // If there are targets, verify they belong to the requested submissions
        foreach ($targets as $target) {
            $this->assertInstanceOf(Target::class, $target);
            $this->assertContains(
                $target->submissionId,
                $submissionIds,
                'Target should belong to one of the requested submissions'
            );
        }
    }

    /**
     * Test target response contains expected fields.
     */
    public function testTargetResponseStructure(): void
    {
        $this->debug('Testing target response structure...');
        $targets = $this->getProcessedTargets();

        if (empty($targets)) {
            $this->markTestSkipped('No processed targets available for structure testing');
        }

        $target = $targets[0];

        // Verify expected fields are present
        $this->assertNotNull($target->targetId, 'targetId should be present');
        $this->assertNotNull($target->submissionId, 'submissionId should be present');
        $this->assertNotNull($target->status, 'status should be present');
        $this->assertNotNull($target->targetLanguage, 'targetLanguage should be present');
        $this->assertNotNull($target->documentName, 'documentName should be present');

        $this->debug("Target structure verified: ID={$target->targetId}, Doc={$target->documentName}");
    }

    /**
     * Test pagination for target polling.
     *
     * Note: Per API documentation, pageSize must be between 20 and 200 (default 50).
     * @see https://onlinehelp.transperfect.com/GLE/RESTAPI/5-using-pagination.html
     */
    public function testGetTargetsWithPagination(): void
    {
        $this->debug('Testing target pagination (pageSize: 20)...');

        // Filter submissions by status to find those likely to have PROCESSED targets
        $submissionRequest = new GetSubmissionsRequest(
            statuses: ['IN_PROCESS', 'READY', 'PROCESSED', 'DELIVERED'],
            pageSize: 20,
            pageNumber: 0
        );
        $submissions = $this->client->getSubmissions($submissionRequest)->submissions;

        if (empty($submissions)) {
            $this->markTestSkipped('No submissions available for pagination testing');
        }

        // Use submission IDs instead of project IDs for filtering
        $submissionIds = array_map(fn($s) => $s->submissionId, array_slice($submissions, 0, 10));
        $this->debug('Using ' . count($submissionIds) . ' submission IDs for filtering');

        // Request first page with minimum allowed page size (20)
        $request = new GetTargetsRequest(
            targetStatus: 'PROCESSED',
            submissionIds: $submissionIds,
            pageSize: 20,
            pageNumber: 0
        );

        $firstPageResponse = $this->client->getTargets($request);

        if ($firstPageResponse->isError()) {
            $this->markTestSkipped('Pagination not supported: ' . $firstPageResponse->errorMessage);
        }

        $firstPage = $firstPageResponse->targets;

        $this->assertIsArray($firstPage);
        $this->assertLessThanOrEqual(20, count($firstPage));
        $this->debug('Page 0: ' . count($firstPage) . ' targets');

        // If we got a full page of results, try second page
        if (count($firstPage) === 20) {
            $this->debug('Full page received, fetching page 1...');
            $request = new GetTargetsRequest(
                targetStatus: 'PROCESSED',
                submissionIds: $submissionIds,
                pageSize: 20,
                pageNumber: 1
            );

            $secondPageResponse = $this->client->getTargets($request);

            if ($secondPageResponse->isError()) {
                $this->markTestSkipped('Pagination not supported: ' . $secondPageResponse->errorMessage);
            }

            $secondPage = $secondPageResponse->targets;

            $this->assertIsArray($secondPage);
            $this->debug('Page 1: ' . count($secondPage) . ' targets');

            // Verify pages don't overlap (if both have results)
            if (!empty($secondPage)) {
                $firstPageIds = array_map(fn($t) => $t->targetId, $firstPage);
                foreach ($secondPage as $target) {
                    $this->assertNotContains(
                        $target->targetId,
                        $firstPageIds,
                        'Second page should not contain targets from first page'
                    );
                }
                $this->debug('Verified no overlap between pages');
            }
        }
    }

    /**
     * Get the PROCESSED targets of a submission created for this test class.
     *
     * Downloading a deliverable marks its target as delivered, and older
     * deliverables may no longer be downloadable, so the class creates its
     * own submission once and every test reuses its targets.
     *
     * @return list<Target>
     */
    private function getProcessedTargets(): array
    {
        if (self::$processedTargets === null) {
            $submissionId = $this->createStartedSubmission('Download Test');
            $this->debug("Created submission {$submissionId} for download tests");
            self::$processedTargets = $this->waitForProcessedTargets(
                $submissionId,
                count($this->getTargetLanguages())
            );
        }

        return self::$processedTargets;
    }
}
