<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Tests\Integration;

use PHPUnit\Framework\TestCase;
use GlobalLink\RestClient\GlobalLinkClient;

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

        $projects = $this->client->getProjects();
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
        $directions = $this->client->getProjectLanguageDirections($projectId);

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
        $formats = $this->client->getProjectFileFormats($projectId);

        if (empty($formats)) {
            $this->markTestSkipped('No file formats available for testing');
        }

        return $formats[0]->name;
    }
}
