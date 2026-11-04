<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Request;

/**
 * Request for listing projects with optional filters.
 */
final readonly class ListProjectsRequest
{
    /**
     * @param string|null $projectName Filter by project name
     * @param string|null $shortCode Filter by project short code
     * @param string|null $organizationName Filter by organization name
     * @param int|null $pageSize Minimum is 20, maximum is 200 (API default: 50)
     * @param int|null $pageNumber 0-indexed page number (default: 0)
     * @see https://onlinehelp.transperfect.com/GLE/RESTAPI/5-using-pagination.html
     */
    public function __construct(
        public ?string $projectName = null,
        public ?string $shortCode = null,
        public ?string $organizationName = null,
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
        $params = [];

        if ($this->projectName !== null) {
            $params['projectName'] = $this->projectName;
        }
        if ($this->shortCode !== null) {
            $params['shortCode'] = $this->shortCode;
        }
        if ($this->organizationName !== null) {
            $params['organizationName'] = $this->organizationName;
        }
        if ($this->pageSize !== null) {
            // API requires pageSize between 20-200
            $params['pageSize'] = max(20, min(200, $this->pageSize));
        }
        if ($this->pageNumber !== null) {
            $params['pageNumber'] = max(0, $this->pageNumber);
        }

        return $params;
    }
}
