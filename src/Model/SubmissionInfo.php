<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Model;

/**
 * Represents detailed submission information.
 */
readonly class SubmissionInfo
{
    /**
     * @param list<string> $targetLanguages
     * @param list<int> $owners
     * @param list<SubmissionBatchInfo> $batchInfos
     * @param list<SubmissionReferenceFileItem> $referenceFiles
     * @param list<User> $submitterInfos
     * @param array<string, mixed> $metadata
     * @param array<string, mixed>|null $languageDirections
     * @param array<string, mixed> $customAttributes
     * @param array<string, mixed> $collateralFiles
     * @param array<string, mixed>|string|null $mslaLevel
     */
    public function __construct(
        public string $name,
        public int $submissionId,
        public int $projectId,
        public string $projectName,
        public string $status,
        public string $sourceLanguage,
        public int $dueDate,
        public int $dateCreated,
        public int $fileCount = 0,
        public bool $started = false,
        public bool $template = false,
        public bool $favorite = false,
        public bool $autoQuote = false,
        public bool $quoteEnabled = false,
        public string $budgetStatus = '',
        public string $claimScope = '',
        public ?string $description = null,
        public ?string $quoteScope = null,
        public ?string $instructions = null,
        public ?string $background = null,
        public ?string $projectAClientName = null,
        public ?int $projectAClientId = null,
        public ?string $projectAJobNumber = null,
        public string|array|null $mslaLevel = null,
        public ?string $templateName = null,
        public ?int $dateStarted = null,
        public ?int $dateArchived = null,
        public ?int $dateCompleted = null,
        public ?int $dateQuoteRequested = null,
        public ?int $dateCanceled = null,
        public int $originalDueDate = 0,
        public mixed $pendingRequestQuote = null,
        public ?SubmissionTechTracking $techTracking = null,
        public array $targetLanguages = [],
        public array $owners = [],
        public array $batchInfos = [],
        public array $referenceFiles = [],
        public array $submitterInfos = [],
        public array $metadata = [],
        public ?array $languageDirections = null,
        public array $customAttributes = [],
        public array $collateralFiles = [],
    ) {
    }

    /**
     * Create a SubmissionInfo from a JSON string.
     */
    public static function fromJson(string $json): self
    {
        if (!json_validate($json)) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        $data = json_decode($json, true);
        return self::fromArray($data);
    }

    /**
     * Create a SubmissionInfo from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $batchInfos = [];
        if (!empty($data['batchInfos']) && is_array($data['batchInfos'])) {
            foreach ($data['batchInfos'] as $bi) {
                $batchInfos[] = SubmissionBatchInfo::fromArray($bi);
            }
        }

        $referenceFiles = [];
        if (!empty($data['referenceFiles']) && is_array($data['referenceFiles'])) {
            foreach ($data['referenceFiles'] as $rf) {
                $referenceFiles[] = SubmissionReferenceFileItem::fromArray($rf);
            }
        }

        $submitterInfos = [];
        if (!empty($data['submitterInfos']) && is_array($data['submitterInfos'])) {
            foreach ($data['submitterInfos'] as $si) {
                $submitterInfos[] = User::fromArray($si);
            }
        }

        $techTracking = null;
        if (isset($data['techTracking']) && is_array($data['techTracking'])) {
            $techTracking = SubmissionTechTracking::fromArray($data['techTracking']);
        }

        return new self(
            name: $data['name'] ?? '',
            submissionId: $data['submissionId'] ?? 0,
            projectId: $data['projectId'] ?? 0,
            projectName: $data['projectName'] ?? '',
            status: $data['status'] ?? '',
            sourceLanguage: $data['sourceLanguage'] ?? '',
            dueDate: $data['dueDate'] ?? 0,
            dateCreated: $data['dateCreated'] ?? 0,
            fileCount: $data['fileCount'] ?? 0,
            started: $data['started'] ?? false,
            template: $data['template'] ?? false,
            favorite: $data['favorite'] ?? false,
            autoQuote: $data['autoQuote'] ?? false,
            quoteEnabled: $data['quoteEnabled'] ?? false,
            budgetStatus: $data['budgetStatus'] ?? '',
            claimScope: $data['claimScope'] ?? '',
            description: $data['description'] ?? null,
            quoteScope: $data['quoteScope'] ?? null,
            instructions: $data['instructions'] ?? null,
            background: $data['background'] ?? null,
            projectAClientName: $data['projectAClientName'] ?? null,
            projectAClientId: $data['projectAClientId'] ?? null,
            projectAJobNumber: $data['projectAJobNumber'] ?? null,
            mslaLevel: $data['mslaLevel'] ?? null,
            templateName: $data['templateName'] ?? null,
            dateStarted: $data['dateStarted'] ?? null,
            dateArchived: $data['dateArchived'] ?? null,
            dateCompleted: $data['dateCompleted'] ?? null,
            dateQuoteRequested: $data['dateQuoteRequested'] ?? null,
            dateCanceled: $data['dateCanceled'] ?? null,
            originalDueDate: $data['originalDueDate'] ?? 0,
            pendingRequestQuote: $data['pendingRequestQuote'] ?? null,
            techTracking: $techTracking,
            targetLanguages: $data['targetLanguages'] ?? [],
            owners: $data['owners'] ?? [],
            batchInfos: $batchInfos,
            referenceFiles: $referenceFiles,
            submitterInfos: $submitterInfos,
            metadata: $data['metadata'] ?? [],
            languageDirections: $data['languageDirections'] ?? null,
            customAttributes: $data['customAttributes'] ?? [],
            collateralFiles: $data['collateralFiles'] ?? [],
        );
    }
}
