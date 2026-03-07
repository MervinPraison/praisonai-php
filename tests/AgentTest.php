<?php

declare(strict_types=1);

namespace PraisonAI\Tests;

use PHPUnit\Framework\TestCase;
use PraisonAI\Agent;
use PraisonAI\Tool;

class AgentTest extends TestCase
{
    public function testAgentConstructorDefaults(): void
    {
        $agent = new Agent();

        $this->assertEquals('Agent', $agent->name);
        $this->assertEquals('You are a helpful assistant.', $agent->instructions);
        $this->assertEquals('gpt-4o', $agent->model);
        $this->assertFalse($agent->verbose);
        $this->assertEmpty($agent->getTools());
    }

    public function testAgentConstructorWithConfig(): void
    {
        $agent = new Agent([
            'name' => 'TestBot',
            'instructions' => 'Be helpful.',
            'model' => 'gpt-4o-mini',
            'verbose' => true,
            'role' => 'Tester',
            'goal' => 'Test things',
            'backstory' => 'A testing agent.',
        ]);

        $this->assertEquals('TestBot', $agent->name);
        $this->assertEquals('Be helpful.', $agent->instructions);
        $this->assertEquals('gpt-4o-mini', $agent->model);
        $this->assertTrue($agent->verbose);
        $this->assertEquals('Tester', $agent->role);
        $this->assertEquals('Test things', $agent->goal);
        $this->assertEquals('A testing agent.', $agent->backstory);
    }

    public function testAgentAddTool(): void
    {
        $agent = new Agent(['name' => 'ToolBot']);

        $tool = Tool::create(
            'test_tool',
            'A test tool',
            fn() => 'hello',
        );

        $agent->addTool($tool);

        $this->assertCount(1, $agent->getTools());
        $this->assertEquals('test_tool', $agent->getTools()[0]->getName());
    }

    public function testToolToOpenAIFormat(): void
    {
        $tool = Tool::create(
            'search',
            'Search the web',
            fn(string $query) => "Results for: {$query}",
            ['query' => ['type' => 'string', 'description' => 'Search query']],
            ['query']
        );

        $schema = $tool->toOpenAI();

        $this->assertEquals('function', $schema['type']);
        $this->assertEquals('search', $schema['function']['name']);
        $this->assertEquals('Search the web', $schema['function']['description']);
        $this->assertArrayHasKey('query', $schema['function']['parameters']['properties']);
        $this->assertContains('query', $schema['function']['parameters']['required']);
    }

    public function testToolExecute(): void
    {
        $tool = Tool::create(
            'greet',
            'Greet someone',
            fn(string $name) => "Hello, {$name}!",
            ['name' => ['type' => 'string', 'description' => 'Name to greet']],
            ['name']
        );

        $result = $tool->execute(['World']);

        $this->assertEquals('Hello, World!', $result);
    }
}
