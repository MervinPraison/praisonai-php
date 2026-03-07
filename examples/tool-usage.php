<?php

/**
 * PraisonAI PHP - Tool Usage Example
 *
 * Demonstrates creating an agent with custom tools for function calling.
 *
 * Usage:
 *   export OPENAI_API_KEY='your-api-key'
 *   php examples/tool-usage.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PraisonAI\Agent;
use PraisonAI\Tool;

// Create a search tool
$searchTool = Tool::create(
    'search_web',
    'Search the web for information',
    function (string $query): string {
        // Simulate a web search
        return "Search results for: {$query}\n- Result 1: Latest findings about {$query}\n- Result 2: Research papers on {$query}\n- Result 3: News articles about {$query}";
    },
    [
        'query' => ['type' => 'string', 'description' => 'The search query'],
    ],
    ['query']
);

// Create a calculator tool
$calculatorTool = Tool::create(
    'calculate',
    'Perform a mathematical calculation',
    function (string $expression): string {
        try {
            // Simple eval for demo purposes
            $result = eval ("return {$expression};");
            return "Result: {$result}";
        } catch (\Throwable $e) {
            return "Error: Could not evaluate expression.";
        }
    },
    [
        'expression' => ['type' => 'string', 'description' => 'A mathematical expression to evaluate'],
    ],
    ['expression']
);

$agent = new Agent([
    'name' => 'ResearchAssistant',
    'instructions' => 'You are a helpful research assistant. Use the available tools to answer questions.',
    'tools' => [$searchTool, $calculatorTool],
    'verbose' => true,
]);

echo "Starting tool usage example...\n";
$response = $agent->chat('Search for the latest developments in quantum computing.');
echo "\n--- Response ---\n";
echo $response . "\n";
