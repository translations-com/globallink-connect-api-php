<?php

declare(strict_types=1);

namespace TransPerfect\GlobalLink\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use TransPerfect\GlobalLink\Model\MslaLevel;

/**
 * Unit tests for MslaLevel model.
 */
class MslaLevelTest extends TestCase
{
    public function testConstructor(): void
    {
        $level = new MslaLevel(
            mslaLevelId: 123,
            name: 'Rush',
            priority: 'RUSH',
            default: false,
            description: 'Rush priority level',
            resourceId: 'res-001'
        );

        $this->assertSame(123, $level->mslaLevelId);
        $this->assertSame('Rush', $level->name);
        $this->assertSame('RUSH', $level->priority);
        $this->assertFalse($level->default);
        $this->assertSame('Rush priority level', $level->description);
        $this->assertSame('res-001', $level->resourceId);
    }

    public function testConstructorWithDefaults(): void
    {
        $level = new MslaLevel(
            mslaLevelId: 456,
            name: 'Standard',
            priority: 'STANDARD'
        );

        $this->assertSame(456, $level->mslaLevelId);
        $this->assertSame('Standard', $level->name);
        $this->assertSame('STANDARD', $level->priority);
        $this->assertFalse($level->default);
        $this->assertNull($level->description);
        $this->assertNull($level->resourceId);
    }

    public function testFromArray(): void
    {
        $data = [
            'mslaLevelId' => 789,
            'name' => 'Low',
            'priority' => 'LOW',
            'default' => true,
            'description' => 'Low priority',
            'resourceId' => null,
        ];

        $level = MslaLevel::fromArray($data);

        $this->assertSame(789, $level->mslaLevelId);
        $this->assertSame('Low', $level->name);
        $this->assertSame('LOW', $level->priority);
        $this->assertTrue($level->default);
        $this->assertSame('Low priority', $level->description);
        $this->assertNull($level->resourceId);
    }

    public function testFromArrayWithMissingFields(): void
    {
        $data = [];

        $level = MslaLevel::fromArray($data);

        $this->assertSame(0, $level->mslaLevelId);
        $this->assertSame('', $level->name);
        $this->assertSame('', $level->priority);
        $this->assertFalse($level->default);
        $this->assertNull($level->description);
        $this->assertNull($level->resourceId);
    }

    public function testFromArrayWithPartialData(): void
    {
        $data = [
            'mslaLevelId' => 100,
            'name' => 'Custom',
        ];

        $level = MslaLevel::fromArray($data);

        $this->assertSame(100, $level->mslaLevelId);
        $this->assertSame('Custom', $level->name);
        $this->assertSame('', $level->priority);
        $this->assertFalse($level->default);
    }
}
