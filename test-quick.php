<?php
/**
 * Quick verification test for praisonai-php with real API key
 */
require_once __DIR__ . '/vendor/autoload.php';

use PraisonAI\Agent;

$apiKey = getenv('OPENAI_API_KEY');
if (!$apiKey) {
    echo "ERROR: OPENAI_API_KEY not set\n";
    exit(1);
}
echo "API Key found: " . substr($apiKey, 0, 7) . "...\n";

// Test 1: Simple agent chat
echo "\n--- Test 1: Simple Agent Chat ---\n";
try {
    $agent = new Agent([
        'name' => 'TestAgent',
        'instructions' => 'You are a helpful assistant. Keep responses very brief (1-2 sentences).',
        'model' => 'gpt-4o-mini',
        'verbose' => true,
    ]);

    $response = $agent->chat('Say hello and tell me you are working.');
    echo "Response: " . $response . "\n";
    echo "✅ Test 1 PASSED\n";
} catch (\Throwable $e) {
    echo "❌ Test 1 FAILED: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}

// Test 2: Tool usage
echo "\n--- Test 2: Agent with Tool ---\n";
try {
    $searchTool = \PraisonAI\Tool::create(
        'search_songs',
        'Search for Tamil Christian songs by keyword',
        function (string $query): string {
            return json_encode([
                ['title' => 'Aadhiyum Anthavum Neerae', 'artist' => 'Pastor'],
                ['title' => 'Yesu Piranthar', 'artist' => 'Unknown'],
            ]);
        },
        ['query' => ['type' => 'string', 'description' => 'Search query for songs']],
        ['query']
    );

    $agent = new Agent([
        'name' => 'SongSearchAgent',
        'instructions' => 'You help find Tamil Christian songs. Use the search_songs tool to find songs. Keep responses brief.',
        'model' => 'gpt-4o-mini',
        'tools' => [$searchTool],
        'verbose' => true,
    ]);

    $response = $agent->chat('Find me songs about Jesus');
    echo "Response: " . $response . "\n";
    echo "✅ Test 2 PASSED\n";
} catch (\Throwable $e) {
    echo "❌ Test 2 FAILED: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}

echo "\n--- All tests complete ---\n";
