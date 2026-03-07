<?php

declare(strict_types=1);

namespace PraisonAI;

/**
 * AgentTeam - Orchestrates multiple Agents or Tasks in sequence.
 *
 * Passes the output of each step as `{previous_result}` to the next,
 * mirroring the TypeScript and Python SDK behavior.
 */
class AgentTeam
{
    /** @var Agent[] */
    private array $agents;

    /** @var (Task|string)[] */
    private array $tasks;

    private bool $verbose;

    /**
     * @param array{
     *   agents?: Agent[],
     *   tasks?: (Task|string)[],
     *   verbose?: bool,
     * } $config
     */
    public function __construct(array $config = [])
    {
        $this->agents = $config['agents'] ?? [];
        $this->tasks = $config['tasks'] ?? [];
        $this->verbose = $config['verbose'] ?? false;

        // If Task objects are provided, extract agents from them
        if (empty($this->agents)) {
            foreach ($this->tasks as $task) {
                if ($task instanceof Task && $task->agent !== null) {
                    $this->agents[] = $task->agent;
                }
            }
        }
    }

    /**
     * Execute all agents/tasks sequentially, passing results forward.
     *
     * @return array<string, string> Map of agent/task name => result
     */
    public function start(): array
    {
        $results = [];
        $previousResult = '';

        $count = max(count($this->agents), count($this->tasks));

        for ($i = 0; $i < $count; $i++) {
            $agent = $this->agents[$i] ?? null;
            $task = $this->tasks[$i] ?? null;

            // Determine the prompt
            $prompt = '';
            if ($task instanceof Task) {
                $prompt = $task->description;
                $agent = $task->agent ?? $agent;
            } elseif (is_string($task)) {
                $prompt = $task;
            }

            if ($agent === null) {
                continue;
            }

            // Substitute {previous_result} placeholder
            if ($previousResult !== '') {
                $prompt = str_replace('{previous_result}', $previousResult, $prompt);

                // Also substitute in agent instructions if needed
                $instructions = $agent->instructions;
                if (str_contains($instructions, '{previous_result}')) {
                    $instructions = str_replace('{previous_result}', $previousResult, $instructions);
                }
            }

            // Use prompt if available, otherwise fall back to agent instructions
            $message = $prompt !== '' ? $prompt : $agent->instructions;

            if ($this->verbose) {
                $label = $task instanceof Task ? $task->name : $agent->name;
                echo "\n{'='*60}\n";
                echo "Running: {$label}\n";
                echo str_repeat('=', 60) . "\n";
            }

            $result = $agent->chat($message);
            $previousResult = $result;

            $name = $task instanceof Task ? $task->name : $agent->name;
            $results[$name] = $result;

            if ($this->verbose) {
                echo "[{$name}] Completed.\n";
            }
        }

        return $results;
    }
}
