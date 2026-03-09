<?php

declare(strict_types=1);

namespace PraisonAI;

use OpenAI\Client;

/**
 * Agent - The core building block of the PraisonAI framework.
 *
 * An Agent encapsulates instructions, an LLM model, and optional tools
 * to perform AI-powered tasks.
 */
class Agent
{
    public readonly string $name;
    public readonly string $instructions;
    public readonly string $model;
    public readonly bool $verbose;
    public readonly string $role;
    public readonly string $goal;
    public readonly string $backstory;

    /** @var Tool[] */
    private array $tools = [];
    private Client $client;

    /** @var array<array{role: string, content: string}> */
    private array $conversationHistory = [];

    /**
     * @param array{
     *   name?: string,
     *   instructions?: string,
     *   model?: string,
     *   tools?: Tool[],
     *   verbose?: bool,
     *   role?: string,
     *   goal?: string,
     *   backstory?: string,
     * } $config
     */
    public function __construct(array $config = [])
    {
        $this->name = $config['name'] ?? 'Agent';
        $this->instructions = $config['instructions'] ?? 'You are a helpful assistant.';
        $this->model = $config['model'] ?? PraisonAI::getDefaultModel();
        $this->verbose = $config['verbose'] ?? false;
        $this->role = $config['role'] ?? '';
        $this->goal = $config['goal'] ?? '';
        $this->backstory = $config['backstory'] ?? '';
        $this->tools = $config['tools'] ?? [];
        $this->client = PraisonAI::client();
    }

    /**
     * Send a message and get a response from the LLM.
     */
    public function chat(string $message): string
    {
        $systemPrompt = $this->buildSystemPrompt();

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Add conversation history
        foreach ($this->conversationHistory as $msg) {
            $messages[] = $msg;
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        $params = [
            'model' => $this->model,
            'messages' => $messages,
        ];

        // Add tools if registered
        if (!empty($this->tools)) {
            $params['tools'] = array_map(fn(Tool $t) => $t->toOpenAI(), $this->tools);
        }

        if ($this->verbose) {
            echo "[{$this->name}] Sending message to {$this->model}...\n";
        }

        $response = $this->client->chat()->create($params);

        $choice = $response->choices[0];

        // Handle tool calls (with multi-step loop)
        if (isset($choice->message->toolCalls) && !empty($choice->message->toolCalls)) {
            $content = $this->handleToolCalls($choice->message->toolCalls, $messages);
        } else {
            $content = $choice->message->content ?? '';
        }

        // Always store in history — regardless of whether tools were used
        $this->conversationHistory[] = ['role' => 'user', 'content' => $message];
        $this->conversationHistory[] = ['role' => 'assistant', 'content' => $content];

        if ($this->verbose) {
            echo "[{$this->name}] Response received.\n";
        }

        return $content;
    }

    /**
     * Alias for chat().
     */
    public function start(string $message): string
    {
        return $this->chat($message);
    }

    /**
     * Add a tool to this agent.
     */
    public function addTool(Tool $tool): self
    {
        $this->tools[] = $tool;
        return $this;
    }

    /**
     * Get registered tools.
     *
     * @return Tool[]
     */
    public function getTools(): array
    {
        return $this->tools;
    }

    /**
     * Set conversation history (e.g. from a previous session).
     *
     * @param array<array{role: string, content: string}> $history
     */
    public function setHistory(array $history): self
    {
        $this->conversationHistory = $history;
        return $this;
    }

    /**
     * Get current conversation history.
     *
     * @return array<array{role: string, content: string}>
     */
    public function getHistory(): array
    {
        return $this->conversationHistory;
    }

    /**
     * Clear conversation history.
     */
    public function clearHistory(): void
    {
        $this->conversationHistory = [];
    }

    /**
     * Build the system prompt from agent configuration.
     */
    private function buildSystemPrompt(): string
    {
        $parts = [$this->instructions];

        if ($this->role !== '') {
            $parts[] = "Your role: {$this->role}";
        }
        if ($this->goal !== '') {
            $parts[] = "Your goal: {$this->goal}";
        }
        if ($this->backstory !== '') {
            $parts[] = "Background: {$this->backstory}";
        }

        return implode("\n\n", $parts);
    }

    /**
     * Handle tool calls from the LLM response.
     * Supports multi-step: if the LLM's follow-up response requests
     * more tool calls, we loop (up to $maxIterations rounds).
     *
     * @param array $toolCalls
     * @param array $messages
     */
    private function handleToolCalls(array $toolCalls, array $messages, int $maxIterations = 5): string
    {
        for ($i = 0; $i < $maxIterations; $i++) {
            // Add the assistant message with tool calls
            $messages[] = [
                'role' => 'assistant',
                'content' => null,
                'tool_calls' => array_map(fn($tc) => [
                    'id' => $tc->id,
                    'type' => 'function',
                    'function' => [
                        'name' => $tc->function->name,
                        'arguments' => $tc->function->arguments,
                    ],
                ], $toolCalls),
            ];

            // Execute each tool call
            foreach ($toolCalls as $toolCall) {
                $functionName = $toolCall->function->name;
                $arguments = json_decode($toolCall->function->arguments, true) ?? [];

                $tool = $this->findTool($functionName);
                if ($tool !== null) {
                    if ($this->verbose) {
                        echo "[{$this->name}] Calling tool: {$functionName}\n";
                    }
                    $result = $tool->execute($arguments);
                } else {
                    $result = "Error: Tool '{$functionName}' not found.";
                }

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall->id,
                    'content' => $result,
                ];
            }

            // Send back to LLM with tool results
            $params = [
                'model' => $this->model,
                'messages' => $messages,
            ];

            if (!empty($this->tools)) {
                $params['tools'] = array_map(fn(Tool $t) => $t->toOpenAI(), $this->tools);
            }

            $response = $this->client->chat()->create($params);
            $choice = $response->choices[0];

            // If LLM wants more tool calls, loop
            if (isset($choice->message->toolCalls) && !empty($choice->message->toolCalls)) {
                $toolCalls = $choice->message->toolCalls;
                continue;
            }

            // LLM returned a final text response
            return $choice->message->content ?? '';
        }

        // Max iterations reached — return whatever we have
        return 'I was unable to complete the request after multiple tool calls. Please try again.';
    }

    /**
     * Find a tool by name.
     */
    private function findTool(string $name): ?Tool
    {
        foreach ($this->tools as $tool) {
            if ($tool->getName() === $name) {
                return $tool;
            }
        }
        return null;
    }
}
