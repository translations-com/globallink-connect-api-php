<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Request;

/**
 * Request for getting submissions with filters.
 *
 * Pagination: pageSize must be between 20-200 (default: 50), pageNumber is 0-indexed.
 *
 * @see https://onlinehelp.transperfect.com/GLE/RESTAPI/5-using-pagination.html
 */
final readonly class GetSubmissionsRequest
{
    /**
     * Allowed status values: CANCELLED, CREATING, PREFLIGHT, DELIVERED, IN_PROCESS, PROCESSED, PROCESSING, READY, WAITING
     * Allowed budget values: ACCEPTED, PROVIDED, APPROVED, FINALIZED, PO_REQUESTED, PO_SENT, INVOICING_AVAILABLE, REQUESTED
     *
     * @param list<string>|null $statuses
     * @param list<string>|null $quoteStatuses
     * @param list<string>|null $budgetStatuses
     * @param list<string>|null $sourceLanguages
     * @param list<string>|null $targetLanguages
     * @param list<string>|null $submissionIds
     * @param int|null $pageSize Minimum is 20, maximum is 200 (API default: 50)
     * @param int|null $pageNumber 0-indexed page number (default: 0)
     */
    public function __construct(
        public ?int $projectId = null,
        public ?string $name = null,
        public ?bool $getBatchInfos = null,
        public ?bool $getCustomAttributes = null,
        public ?array $submissionIds = null,
        public ?string $projectName = null,
        public ?string $paClientName = null,
        public ?array $statuses = null,
        public ?array $quoteStatuses = null,
        public ?bool $readyForQuoteReview = null,
        public ?array $budgetStatuses = null,
        public ?array $sourceLanguages = null,
        public ?array $targetLanguages = null,
        public ?int $dateCreatedFrom = null,
        public ?int $dateCreatedTo = null,
        public ?int $dueDateFrom = null,
        public ?int $dueDateTo = null,
        public ?string $orderBy = null,
        public ?string $orderType = null,
        public ?int $pageSize = null,
        public ?int $pageNumber = null,
    ) {
    }

    /**
     * Convert to query parameters array.
     *
     * @return array<string, mixed>
     */
    public function toQueryParams(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->getBatchInfos !== null) {
            $data['getBatchInfos'] = $this->getBatchInfos;
        }
        if ($this->getCustomAttributes !== null) {
            $data['getCustomAttributes'] = $this->getCustomAttributes;
        }
        if ($this->submissionIds !== null) {
            $data['submissionIds'] = implode(',', $this->submissionIds);
        }
        if ($this->projectName !== null) {
            $data['projectName'] = $this->projectName;
        }
        if ($this->paClientName !== null) {
            $data['paClientName'] = $this->paClientName;
        }
        if ($this->statuses !== null) {
            $data['statuses'] = implode(',', $this->statuses);
        }
        if ($this->quoteStatuses !== null) {
            $data['quoteStatuses'] = implode(',', $this->quoteStatuses);
        }
        if ($this->readyForQuoteReview !== null) {
            $data['readyForQuoteReview'] = $this->readyForQuoteReview;
        }
        if ($this->budgetStatuses !== null) {
            $data['budgetStatuses'] = implode(',', $this->budgetStatuses);
        }
        if ($this->sourceLanguages !== null) {
            $data['sourceLanguages'] = implode(',', $this->sourceLanguages);
        }
        if ($this->targetLanguages !== null) {
            $data['targetLanguages'] = implode(',', $this->targetLanguages);
        }
        if ($this->dateCreatedFrom !== null) {
            $data['dateCreatedFrom'] = $this->dateCreatedFrom;
        }
        if ($this->dateCreatedTo !== null) {
            $data['dateCreatedTo'] = $this->dateCreatedTo;
        }
        if ($this->dueDateFrom !== null) {
            $data['dueDateFrom'] = $this->dueDateFrom;
        }
        if ($this->dueDateTo !== null) {
            $data['dueDateTo'] = $this->dueDateTo;
        }
        if ($this->orderBy !== null) {
            $data['orderBy'] = $this->orderBy;
        }
        if ($this->orderType !== null) {
            $data['orderType'] = $this->orderType;
        }
        if ($this->pageSize !== null) {
            // API requires pageSize between 20-200
            $data['pageSize'] = max(20, min(200, $this->pageSize));
        }
        if ($this->pageNumber !== null) {
            $data['pageNumber'] = max(0, $this->pageNumber);
        }

        return $data;
    }
}
