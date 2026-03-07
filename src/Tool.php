<?php

declare(strict_types=1);

namespace PraisonAI;

/**
 * Tool - Wraps a callable with an OpenAI function-calling schema.
 */
class Tool
{
    private string $name;
    private string $description;
    /** @var callable */
    private $callable;
    private array $parameters;

    public function __construct(string $name, string $description, callable $callable, array $parameters = [])
    {
        $this->name = $name;
        $this->description = $description;
        $this->callable = $callable;
        $this->parameters = $parameters;
    }

    /**
     * Convenience factory method.
     *
     * @param array $parameters JSON Schema properties definition, e.g.:
     *   [
     *     'query' => ['type' => 'string', 'description' => 'Search query'],
     *   ]
     */
    public static function create(string $name, string $description, callable $callable, array $parameters = [], array $required = []): self
    {
        $schema = [
            'type' => 'object',
            'properties' => $parameters,
        ];
        if (!empty($required)) {
            $schema['required'] = $required;
        }

        return new self($name, $description, $callable, $schema);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Convert to OpenAI function-calling tool format.
     */
    public function toOpenAI(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name,
                'description' => $this->description,
                'parameters' => $this->parameters ?: [
                    'type' => 'object',
                    'properties' => new \stdClass(),
                ],
            ],
        ];
    }

    /**
     * Execute the tool with the given arguments.
     */
    public function execute(array $arguments = []): string
    {
        $result = call_user_func($this->callable, ...$arguments);

        return is_string($result) ? $result : json_encode($result);
    }
}
