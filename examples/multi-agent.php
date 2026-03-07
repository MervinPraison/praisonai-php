<?php

/**
 * PraisonAI PHP - Multi-Agent Example
 *
 * Usage:
 *   export OPENAI_API_KEY='your-api-key'
 *   php examples/multi-agent.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PraisonAI\Agent;
use PraisonAI\AgentTeam;

$researchAgent = new Agent([
    'name' => 'ResearchAgent',
    'instructions' => 'Research and provide detailed information about renewable energy sources.',
    'verbose' => true,
]);

$summaryAgent = new Agent([
    'name' => 'SummaryAgent',
    'instructions' => 'Create a concise summary of the research findings about renewable energy sources. Use {previous_result} as input.',
    'verbose' => true,
]);

$recommendationAgent = new Agent([
    'name' => 'RecommendationAgent',
    'instructions' => 'Based on the summary in {previous_result}, provide specific recommendations for implementing renewable energy solutions.',
    'verbose' => true,
]);

$team = new AgentTeam([
    'agents' => [$researchAgent, $summaryAgent, $recommendationAgent],
    'tasks' => [
        'Research and analyze current renewable energy technologies and their implementation.',
        'Summarize the key findings from the research.',
        'Provide actionable recommendations based on the summary.',
    ],
    'verbose' => true,
]);

echo "Starting multi-agent example...\n";
$results = $team->start();

foreach ($results as $name => $result) {
    echo "\n--- {$name} ---\n";
    echo $result . "\n";
}
