<?php

declare(strict_types=1);

namespace GlobalLink\RestClient\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use GlobalLink\RestClient\Model\Project;

/**
 * Unit tests for Project model.
 */
class ProjectTest extends TestCase
{
    public function testFromArrayCreatesProject(): void
    {
        $data = [
            'name' => 'Test Project',
            'projectId' => 1001,
            'organizationId' => 100,
            'shortCode' => 'TP1',
            'enabled' => true,
            'quoteEnabled' => true,
            'quoteScope' => 'PROJECT',
            'autoQuote' => false,
            'costScopeId' => 1,
            'autoStartSubmission' => false,
            'defaultWorkflowDefinitionId' => 200,
            'displayMslaLevel' => true,
        ];

        $project = Project::fromArray($data);

        $this->assertSame('Test Project', $project->name);
        $this->assertSame(1001, $project->projectId);
        $this->assertSame(100, $project->organizationId);
        $this->assertSame('TP1', $project->shortCode);
        $this->assertTrue($project->enabled);
        $this->assertTrue($project->quoteEnabled);
        $this->assertSame('PROJECT', $project->quoteScope);
        $this->assertFalse($project->autoQuote);
        $this->assertSame(1, $project->costScopeId);
        $this->assertFalse($project->autoStartSubmission);
        $this->assertSame(200, $project->defaultWorkflowDefinitionId);
        $this->assertTrue($project->displayMslaLevel);
    }

    public function testFromArrayWithMissingFieldsUsesDefaults(): void
    {
        $data = [
            'name' => 'Minimal Project',
            'projectId' => 1002,
        ];

        $project = Project::fromArray($data);

        $this->assertSame('Minimal Project', $project->name);
        $this->assertSame(1002, $project->projectId);
        $this->assertSame(0, $project->organizationId);
        $this->assertSame('', $project->shortCode);
        $this->assertFalse($project->enabled);
        $this->assertFalse($project->quoteEnabled);
    }

    public function testFromArrayWithEmptyArrayUsesDefaults(): void
    {
        $project = Project::fromArray([]);

        $this->assertSame('', $project->name);
        $this->assertSame(0, $project->projectId);
        $this->assertSame(0, $project->organizationId);
        $this->assertFalse($project->enabled);
    }
}
