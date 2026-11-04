<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use GlobalLink\RestClient\Exception\InvalidArgumentException;
use GlobalLink\RestClient\Model\TechTracking;

/**
 * Unit tests for TechTracking model.
 */
class TechTrackingTest extends TestCase
{
    public function testConstructorValidatesAdaptorNameLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('adaptorName max length is 50');

        new TechTracking(
            str_repeat('a', 51),
            'v1.0.0',
            'v1.0.0',
            'PHP'
        );
    }

    public function testConstructorValidatesAdaptorVersionLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('adaptorVersion max length is 20');

        new TechTracking(
            'Test Adaptor',
            str_repeat('v', 21),
            'v1.0.0',
            'PHP'
        );
    }

    public function testConstructorValidatesClientVersionLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('clientVersion max length is 20');

        new TechTracking(
            'Test Adaptor',
            'v1.0.0',
            str_repeat('c', 21),
            'PHP'
        );
    }

    public function testConstructorValidatesTechnologyProductLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('technologyProduct max length is 20');

        new TechTracking(
            'Test Adaptor',
            'v1.0.0',
            'v1.0.0',
            str_repeat('t', 21)
        );
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $techTracking = new TechTracking(
            'PHP Connector',
            '1.0.0',
            '8.3.0',
            'PHP'
        );

        $array = $techTracking->toArray();

        $this->assertSame('PHP Connector', $array['adaptorName']);
        $this->assertSame('1.0.0', $array['adaptorVersion']);
        $this->assertSame('8.3.0', $array['clientVersion']);
        $this->assertSame('PHP', $array['technologyProduct']);
    }

    public function testFromArrayCreatesTechTracking(): void
    {
        $data = [
            'adaptorName' => 'PHP Connector',
            'adaptorVersion' => '1.0.0',
            'clientVersion' => '8.3.0',
            'technologyProduct' => 'PHP',
        ];

        $techTracking = TechTracking::fromArray($data);

        $this->assertSame('PHP Connector', $techTracking->adaptorName);
        $this->assertSame('1.0.0', $techTracking->adaptorVersion);
        $this->assertSame('8.3.0', $techTracking->clientVersion);
        $this->assertSame('PHP', $techTracking->technologyProduct);
    }

    public function testRoundTrip(): void
    {
        $original = new TechTracking(
            'Test Adaptor',
            '2.0.0',
            '8.3.1',
            'PHP SDK'
        );

        $array = $original->toArray();
        $restored = TechTracking::fromArray($array);

        $this->assertSame($original->adaptorName, $restored->adaptorName);
        $this->assertSame($original->adaptorVersion, $restored->adaptorVersion);
        $this->assertSame($original->clientVersion, $restored->clientVersion);
        $this->assertSame($original->technologyProduct, $restored->technologyProduct);
    }
}
