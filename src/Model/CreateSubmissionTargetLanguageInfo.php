<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Target language information for creating submissions.
 */
final readonly class CreateSubmissionTargetLanguageInfo
{
    public function __construct(
        public string $targetLanguage,
    ) {
    }

    /**
     * Convert to array format for API requests.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['targetLanguage' => $this->targetLanguage];
    }
}
