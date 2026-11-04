<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use GlobalLink\RestClient\Model\SubmissionWordcount;

/**
 * Unit tests for SubmissionWordcount model.
 */
class SubmissionWordcountTest extends TestCase
{
    public function testConstructor(): void
    {
        $wordcount = new SubmissionWordcount(
            totalWordCount: 10000,
            totalSegments: 500,
            inContextMatch: 1000,
            match100: 2000,
            match95to99: 1500,
            match85to94: 1000,
            match75to84: 500,
            noMatch: 3000,
            repetitions: 1000,
            leverageBreakdown: ['100%' => 2000],
            languageWordcounts: ['de-DE' => 5000, 'fr-FR' => 5000]
        );

        $this->assertSame(10000, $wordcount->totalWordCount);
        $this->assertSame(500, $wordcount->totalSegments);
        $this->assertSame(1000, $wordcount->inContextMatch);
        $this->assertSame(2000, $wordcount->match100);
        $this->assertSame(1500, $wordcount->match95to99);
        $this->assertSame(1000, $wordcount->match85to94);
        $this->assertSame(500, $wordcount->match75to84);
        $this->assertSame(3000, $wordcount->noMatch);
        $this->assertSame(1000, $wordcount->repetitions);
        $this->assertSame(['100%' => 2000], $wordcount->leverageBreakdown);
        $this->assertSame(['de-DE' => 5000, 'fr-FR' => 5000], $wordcount->languageWordcounts);
    }

    public function testConstructorWithDefaults(): void
    {
        $wordcount = new SubmissionWordcount();

        $this->assertSame(0, $wordcount->totalWordCount);
        $this->assertSame(0, $wordcount->totalSegments);
        $this->assertSame(0, $wordcount->inContextMatch);
        $this->assertSame(0, $wordcount->match100);
        $this->assertSame(0, $wordcount->match95to99);
        $this->assertSame(0, $wordcount->match85to94);
        $this->assertSame(0, $wordcount->match75to84);
        $this->assertSame(0, $wordcount->noMatch);
        $this->assertSame(0, $wordcount->repetitions);
        $this->assertSame([], $wordcount->leverageBreakdown);
        $this->assertSame([], $wordcount->languageWordcounts);
    }

    public function testFromArray(): void
    {
        $data = [
            'totalWordCount' => 5000,
            'totalSegments' => 250,
            'inContextMatch' => 500,
            'match100' => 1000,
            'match95to99' => 750,
            'match85to94' => 500,
            'match75to84' => 250,
            'noMatch' => 1500,
            'repetitions' => 500,
            'leverageBreakdown' => ['test' => 100],
            'languageWordcounts' => ['en-US' => 5000],
        ];

        $wordcount = SubmissionWordcount::fromArray($data);

        $this->assertSame(5000, $wordcount->totalWordCount);
        $this->assertSame(250, $wordcount->totalSegments);
        $this->assertSame(500, $wordcount->inContextMatch);
        $this->assertSame(1000, $wordcount->match100);
        $this->assertSame(750, $wordcount->match95to99);
        $this->assertSame(500, $wordcount->match85to94);
        $this->assertSame(250, $wordcount->match75to84);
        $this->assertSame(1500, $wordcount->noMatch);
        $this->assertSame(500, $wordcount->repetitions);
    }

    public function testFromArrayWithAlternateKeys(): void
    {
        // Test that we handle alternate API response keys
        $data = [
            'wordCount' => 3000,  // alternate for totalWordCount
            'segmentCount' => 150, // alternate for totalSegments
            '95% - 99%' => 400,   // alternate key format
            '85% - 94%' => 300,
            '75% - 84%' => 200,
            'leverage' => ['item' => 50], // alternate for leverageBreakdown
            'languages' => ['ja-JP' => 3000], // alternate for languageWordcounts
        ];

        $wordcount = SubmissionWordcount::fromArray($data);

        $this->assertSame(3000, $wordcount->totalWordCount);
        $this->assertSame(150, $wordcount->totalSegments);
        $this->assertSame(400, $wordcount->match95to99);
        $this->assertSame(300, $wordcount->match85to94);
        $this->assertSame(200, $wordcount->match75to84);
        $this->assertSame(['item' => 50], $wordcount->leverageBreakdown);
        $this->assertSame(['ja-JP' => 3000], $wordcount->languageWordcounts);
    }

    public function testFromArrayWithEmptyData(): void
    {
        $wordcount = SubmissionWordcount::fromArray([]);

        $this->assertSame(0, $wordcount->totalWordCount);
        $this->assertSame(0, $wordcount->totalSegments);
        $this->assertSame([], $wordcount->leverageBreakdown);
        $this->assertSame([], $wordcount->languageWordcounts);
    }
}
