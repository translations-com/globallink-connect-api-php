<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Model;

/**
 * Represents wordcount information for a submission.
 */
readonly class SubmissionWordcount
{
    /**
     * @param array<string, mixed> $leverageBreakdown Breakdown by leverage categories
     * @param array<string, mixed> $languageWordcounts Wordcounts per language
     */
    public function __construct(
        public int $totalWordCount = 0,
        public int $totalSegments = 0,
        public int $inContextMatch = 0,
        public int $match100 = 0,
        public int $match95to99 = 0,
        public int $match85to94 = 0,
        public int $match75to84 = 0,
        public int $noMatch = 0,
        public int $repetitions = 0,
        public array $leverageBreakdown = [],
        public array $languageWordcounts = [],
    ) {
    }

    /**
     * Create a SubmissionWordcount from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            totalWordCount: $data['totalWordCount'] ?? $data['wordCount'] ?? 0,
            totalSegments: $data['totalSegments'] ?? $data['segmentCount'] ?? 0,
            inContextMatch: $data['inContextMatch'] ?? 0,
            match100: $data['match100'] ?? 0,
            match95to99: $data['match95to99'] ?? $data['95% - 99%'] ?? 0,
            match85to94: $data['match85to94'] ?? $data['85% - 94%'] ?? 0,
            match75to84: $data['match75to84'] ?? $data['75% - 84%'] ?? 0,
            noMatch: $data['noMatch'] ?? 0,
            repetitions: $data['repetitions'] ?? 0,
            leverageBreakdown: $data['leverageBreakdown'] ?? $data['leverage'] ?? [],
            languageWordcounts: $data['languageWordcounts'] ?? $data['languages'] ?? [],
        );
    }
}
