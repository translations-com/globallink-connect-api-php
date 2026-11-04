<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Request;

/**
 * Request for saving a submission.
 */
final readonly class SaveSubmissionRequest
{
    public function __construct(
        public ?bool $autoStart = null,
    ) {
    }

    /**
     * Convert to query parameters array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->autoStart !== null) {
            $data['autoStart'] = $this->autoStart;
        }

        return $data;
    }
}
