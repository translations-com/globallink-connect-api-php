<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Request;

/**
 * Request for getting targets with filters.
 *
 * Pagination: pageSize must be between 20-200 (default: 50), pageNumber is 0-indexed.
 *
 * @see https://onlinehelp.transperfect.com/GLE/RESTAPI/5-using-pagination.html
 */
final readonly class GetTargetsRequest
{
    /**
     * @param string $targetStatus Target status: PROCESSED, CANCELLED
     * @param list<int>|null $submissionIds Submission IDs to filter by
     * @param list<int>|null $documentIds Document IDs to filter by
     * @param list<int>|null $projectIds Project IDs to filter by
     * @param int|null $pageSize Minimum is 20, maximum is 200 (API default: 50)
     * @param int|null $pageNumber 0-indexed page number (default: 0)
     */
    public function __construct(
        public string $targetStatus,
        public ?array $submissionIds = null,
        public ?array $documentIds = null,
        public ?array $projectIds = null,
        public ?int $pageSize = null,
        public ?int $pageNumber = null,
    ) {
    }

    /**
     * Convert to query parameters array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = ['targetStatus' => $this->targetStatus];

        if ($this->submissionIds !== null) {
            $data['submissionIds'] = implode(',', $this->submissionIds);
        }
        if ($this->documentIds !== null) {
            $data['documentIds'] = implode(',', $this->documentIds);
        }
        if ($this->projectIds !== null) {
            $data['projectIds'] = implode(',', $this->projectIds);
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
