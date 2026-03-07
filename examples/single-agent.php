<?php

/**
 * PraisonAI PHP - Single Agent Example
 *
 * Usage:
 *   export OPENAI_API_KEY='your-api-key'
 *   php examples/single-agent.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PraisonAI\Agent;
use PraisonAI\AgentTeam;

$agent = new Agent([
    'name' => 'BiologyExpert',
    'instructions' => 'Explain the process of photosynthesis in detail.',
    'verbose' => true,
]);

$team = new AgentTeam([
    'agents' => [$agent],
    'tasks' => ['Explain the process of photosynthesis in detail.'],
    'verbose' => true,
]);

echo "Starting single agent example...\n";
$results = $team->start();

foreach ($results as $name => $result) {
    echo "\n--- {$name} ---\n";
    echo $result . "\n";
}
