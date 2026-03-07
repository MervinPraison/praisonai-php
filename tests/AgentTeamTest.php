<?php

declare(strict_types=1);

namespace PraisonAI\Tests;

use PHPUnit\Framework\TestCase;
use PraisonAI\Agent;
use PraisonAI\AgentTeam;
use PraisonAI\Task;

class AgentTeamTest extends TestCase
{
    public function testTeamConstructorDefaults(): void
    {
        $team = new AgentTeam();

        // Should not throw; start returns empty results
        $this->assertIsArray((new \ReflectionClass($team))->getProperty('agents')->getValue($team));
    }

    public function testTaskConstruction(): void
    {
        $agent = new Agent(['name' => 'Worker']);

        $task = new Task([
            'name' => 'Test Task',
            'description' => 'Do something',
            'agent' => $agent,
        ]);

        $this->assertEquals('Test Task', $task->name);
        $this->assertEquals('Do something', $task->description);
        $this->assertSame($agent, $task->agent);
        $this->assertEmpty($task->dependencies);
    }

    public function testTaskDependencies(): void
    {
        $agent = new Agent(['name' => 'Worker']);

        $task1 = new Task([
            'name' => 'First',
            'description' => 'First task',
            'agent' => $agent,
        ]);

        $task2 = new Task([
            'name' => 'Second',
            'description' => 'Depends on first',
            'agent' => $agent,
            'dependencies' => [$task1],
        ]);

        $this->assertCount(1, $task2->dependencies);
        $this->assertSame($task1, $task2->dependencies[0]);
    }
}
