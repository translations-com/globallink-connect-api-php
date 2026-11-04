<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Tests\Integration;

use PHPUnit\Framework\TestCase;
use GlobalLink\RestClient\GlobalLinkClient;
use GlobalLink\RestClient\Model\BatchInfo;
use GlobalLink\RestClient\Model\Target;
use GlobalLink\RestClient\Request\CreateSubmissionRequest;
use GlobalLink\RestClient\Request\GetTargetsRequest;
use GlobalLink\RestClient\Request\SaveSubmissionRequest;
use GlobalLink\RestClient\Request\UploadSourceFileRequest;

/**
 * Base class for integration tests.
 *
 * Integration tests require the following environment variables to be set:
 * - GLE_TEST_API_URL
 * - GLE_TEST_USERNAME
 * - GLE_TEST_PASSWORD
 * - GLE_TEST_OAUTH_CLIENT
 * - GLE_TEST_OAUTH_SECRET
 * - GLE_TEST_PROJECT_ID (optional)
 * - GLE_FILE_FORMAT_NAME (optional)
 * - GLE_SOURCE_LANGUAGE (optional)
 * - GLE_TARGET_LANGUAGES (optional, comma-separated, e.g., "de-DE,ja-JP,fr-FR")
 * - GLE_TEST_FILE_NAME (optional)
 * - GLE_TEST_PROCESSING_TIMEOUT (optional, seconds to wait for targets to be PROCESSED, default 180)
 */
abstract class IntegrationTestCase extends TestCase
{
    protected ?GlobalLinkClient $client = null;
    protected ?int $projectId = null;
    protected ?string $fileFormatName = null;
    protected ?string $sourceLanguage = null;
    /** @var list<string> */
    protected array $targetLanguages = [];
    protected ?string $testFileName = null;

    /**
     * Output debug message to STDERR for visibility during test runs.
     *
     * Messages are shown when running with --verbose or --debug flags.
     */
    protected function debug(string $message): void
    {
        fwrite(STDERR, "\n    → {$message}");
    }

    protected function setUp(): void
    {
        parent::setUp();

        $apiUrl = getenv('GLE_TEST_API_URL');
        $username = getenv('GLE_TEST_USERNAME');
        $password = getenv('GLE_TEST_PASSWORD');
        $oauthClient = getenv('GLE_TEST_OAUTH_CLIENT');
        $oauthSecret = getenv('GLE_TEST_OAUTH_SECRET');

        if (empty($apiUrl) || empty($username) || empty($password) ||
            empty($oauthClient) || empty($oauthSecret)) {
            $this->markTestSkipped(
                'Integration tests require GLE_TEST_* environment variables. ' .
                'Copy .env.test.example to .env.test and configure your test credentials.'
            );
        }

        $this->client = new GlobalLinkClient(
            $apiUrl,
            $username,
            $password,
            $oauthClient,
            $oauthSecret
        );

        // Load optional configuration
        $projectId = getenv('GLE_TEST_PROJECT_ID');
        $this->projectId = $projectId ? (int) $projectId : null;

        $this->fileFormatName = getenv('GLE_FILE_FORMAT_NAME') ?: null;
        $this->sourceLanguage = getenv('GLE_SOURCE_LANGUAGE') ?: 'en-US';
        $this->testFileName = getenv('GLE_TEST_FILE_NAME') ?: null;

        // Parse comma-separated target languages
        $targetLanguagesEnv = getenv('GLE_TARGET_LANGUAGES');
        if ($targetLanguagesEnv) {
            $this->targetLanguages = array_filter(
                array_map('trim', explode(',', $targetLanguagesEnv))
            );
        }
    }

    protected function tearDown(): void
    {
        $this->client = null;
        parent::tearDown();
    }

    /**
     * Get a project ID for testing.
     * Uses GLE_TEST_PROJECT_ID if set, otherwise gets the first available project.
     */
    protected function getTestProjectId(): int
    {
        if ($this->projectId !== null) {
            return $this->projectId;
        }

        $projects = $this->client->getProjects()->projects;
        if (empty($projects)) {
            $this->markTestSkipped('No projects available for testing');
        }

        return $projects[0]->projectId;
    }

    /**
     * Get target languages for testing.
     *
     * Uses GLE_TARGET_LANGUAGES if configured, otherwise falls back to
     * discovering languages from the project's language directions.
     *
     * @return list<string>
     */
    protected function getTargetLanguages(): array
    {
        // Use configured target languages if available
        if (!empty($this->targetLanguages)) {
            return $this->targetLanguages;
        }

        // Fallback: get from project language directions
        $languages = [];
        $projectId = $this->getTestProjectId();
        $directions = $this->client->getProjectLanguageDirections($projectId)->languageDirections;

        foreach ($directions as $direction) {
            if ($direction->sourceLanguage === $this->sourceLanguage) {
                $languages[] = $direction->targetLanguage;
                if (count($languages) >= 2) {
                    break;
                }
            }
        }

        if (empty($languages)) {
            $this->markTestSkipped('No target languages configured for testing');
        }

        return $languages;
    }

    /**
     * Get the path to the test file.
     */
    protected function getTestFilePath(): string
    {
        $fileName = $this->testFileName ?? 'test.xml';
        $path = __DIR__ . '/' . $fileName;

        if (!file_exists($path)) {
            $this->markTestSkipped("Test file not found: {$path}");
        }

        return $path;
    }

    /**
     * Get the file format name for testing.
     */
    protected function getFileFormatName(): string
    {
        if ($this->fileFormatName) {
            return $this->fileFormatName;
        }

        // Fallback: get first file format from project
        $projectId = $this->getTestProjectId();
        $formats = $this->client->getProjectFileFormats($projectId)->fileFormatProfiles;

        if (empty($formats)) {
            $this->markTestSkipped('No file formats available for testing');
        }

        return $formats[0]->name;
    }

    /**
     * Get the default workflow ID for the test project.
     */
    protected function getDefaultWorkflowId(): int
    {
        $projectId = $this->getTestProjectId();
        $workflows = $this->client->getProjectWorkflows($projectId)->workflows;

        if (empty($workflows)) {
            $this->markTestSkipped('No workflows available for testing');
        }

        return $workflows[0]->id;
    }

    /**
     * Create, upload and start a small submission in the test project.
     *
     * @return int The submission ID
     */
    protected function createStartedSubmission(string $name): int
    {
        $batchName = 'Integration Test Batch';
        $targetLanguages = $this->getTargetLanguages();

        $create = $this->client->createSubmission(new CreateSubmissionRequest(
            name: $name . ' ' . date('Y-m-d H:i:s'),
            dueDate: (time() + 86400 * 7) * 1000,
            projectId: $this->getTestProjectId(),
            sourceLanguage: $this->sourceLanguage,
            batchInfos: [new BatchInfo(
                name: $batchName,
                targetLanguages: $targetLanguages,
                workflowDefinitionId: $this->getDefaultWorkflowId()
            )],
            claimScope: 'LANGUAGE',
            instructions: 'PHP SDK integration test submission.'
        ));
        $this->assertTrue($create->isSuccess(), "Create submission failed: {$create->errorMessage}");

        $content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<document><title>Integration Test</title>'
            . '<body><paragraph id="p1">This is a test paragraph for translation.</paragraph></body></document>';

        $upload = $this->client->uploadSubmissionSourceFile($create->submissionId, new UploadSourceFileRequest(
            batchName: $batchName,
            fileContents: $content,
            fileName: 'integration_test.xml',
            fileFormatName: $this->getFileFormatName(),
            targetLanguages: $targetLanguages
        ));
        $this->assertTrue($upload->isSuccess(), "Upload failed: {$upload->errorMessage}");

        $save = $this->client->saveSubmission($create->submissionId, new SaveSubmissionRequest(autoStart: true));
        $this->assertTrue($save->isSuccess(), "Save failed: {$save->errorMessage}");

        return $create->submissionId;
    }

    /**
     * Poll until the submission has the expected number of PROCESSED targets.
     *
     * Waits up to GLE_TEST_PROCESSING_TIMEOUT seconds (default 180) and returns
     * whatever PROCESSED targets exist when the time runs out.
     *
     * @return list<Target>
     */
    protected function waitForProcessedTargets(int $submissionId, int $expectedCount): array
    {
        $timeout = (int) (getenv('GLE_TEST_PROCESSING_TIMEOUT') ?: 180);
        $deadline = time() + $timeout;

        do {
            $targets = $this->client->getTargets(new GetTargetsRequest(
                targetStatus: 'PROCESSED',
                submissionIds: [$submissionId],
                pageSize: 50
            ))->targets;

            if (count($targets) >= $expectedCount) {
                break;
            }

            sleep(10);
        } while (time() < $deadline);

        $this->debug('Submission ' . $submissionId . ': ' . count($targets) . " of {$expectedCount} targets PROCESSED");

        return $targets;
    }
}
