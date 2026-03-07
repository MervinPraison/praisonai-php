# PraisonAI PHP AI Agents Framework

PraisonAI is a production-ready Multi AI Agents framework for PHP, designed to create AI Agents to automate and solve problems ranging from simple tasks to complex challenges. It provides a low-code solution to streamline the building and management of multi-agent LLM systems, emphasising simplicity, customisation, and effective human-agent collaboration.

## Installation

```bash
composer require mervinpraison/praisonai
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use PraisonAI\Agent;

$agent = new Agent([
    'name' => 'MyAssistant',
    'instructions' => 'You are a helpful assistant.',
]);

$response = $agent->chat('Hello!');
echo $response;
```

## Multi-Agent Example

```php
<?php

use PraisonAI\Agent;
use PraisonAI\AgentTeam;

$researcher = new Agent([
    'name' => 'Researcher',
    'instructions' => 'Research renewable energy sources.',
]);

$summarizer = new Agent([
    'name' => 'Summarizer',
    'instructions' => 'Summarize the research in {previous_result}.',
]);

$team = new AgentTeam([
    'agents' => [$researcher, $summarizer],
    'tasks' => [
        'Research current renewable energy technologies.',
        'Summarize the key findings.',
    ],
]);

$results = $team->start();
```

## Task-Based Example

```php
<?php

use PraisonAI\Agent;
use PraisonAI\Task;
use PraisonAI\AgentTeam;

$chef = new Agent([
    'name' => 'Chef',
    'role' => 'Nutrition Expert',
    'goal' => 'Create healthy recipes',
    'instructions' => 'Create 3 healthy recipes with nutritional info.',
]);

$blogger = new Agent([
    'name' => 'Blogger',
    'role' => 'Food Blogger',
    'instructions' => 'Write a blog post about the recipes.',
]);

$recipeTask = new Task([
    'name' => 'Create Recipes',
    'description' => 'Create 3 healthy recipes',
    'agent' => $chef,
]);

$blogTask = new Task([
    'name' => 'Write Blog',
    'description' => 'Write a blog post about the recipes in {previous_result}',
    'agent' => $blogger,
    'dependencies' => [$recipeTask],
]);

$team = new AgentTeam([
    'tasks' => [$recipeTask, $blogTask],
]);

$results = $team->start();
```

## Tool Usage

```php
<?php

use PraisonAI\Agent;
use PraisonAI\Tool;

$searchTool = Tool::create(
    'search_web',
    'Search the web for information',
    fn(string $query) => "Results for: {$query}",
    ['query' => ['type' => 'string', 'description' => 'Search query']],
    ['query']
);

$agent = new Agent([
    'name' => 'Assistant',
    'instructions' => 'Use tools to answer questions.',
    'tools' => [$searchTool],
]);

$response = $agent->chat('Search for quantum computing news.');
```

## API Parity

| Feature | Python | TypeScript | Rust | **PHP** |
|---------|--------|------------|------|---------|
| Agent | ✅ | ✅ | ✅ | ✅ |
| Task | ✅ | ✅ | ✅ | ✅ |
| AgentTeam | ✅ | ✅ | ✅ | ✅ |
| Tools | ✅ | ✅ | ✅ | ✅ |
| Streaming | ✅ | ✅ | ✅ | 🔜 |
| Memory | ✅ | ✅ | ✅ | 🔜 |

## Environment Variables

```bash
export OPENAI_API_KEY='your-api-key'
```

## Requirements

- PHP 8.2+
- Composer

## Package Structure

```
praisonai-php/
├── composer.json
├── phpunit.xml
├── README.md
├── src/
│   ├── Agent.php
│   ├── AgentTeam.php
│   ├── PraisonAI.php
│   ├── Task.php
│   └── Tool.php
├── examples/
│   ├── single-agent.php
│   ├── multi-agent.php
│   └── tool-usage.php
└── tests/
    ├── AgentTest.php
    └── AgentTeamTest.php
```

## License

MIT
