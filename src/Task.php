<?php

declare(strict_types=1);

namespace PraisonAI;

/**
 * Task - Represents a unit of work assigned to an Agent.
 */
class Task
{
    public readonly string $name;
    public readonly string $description;
    public readonly ?Agent $agent;
    /** @var Task[] */
    public readonly array $dependencies;

    /**
     * @param array{
     *   name: string,
     *   description: string,
     *   agent?: Agent,
     *   dependencies?: Task[],
     * } $config
     */
    public function __construct(array $config)
    {
        $this->name = $config['name'];
        $this->description = $config['description'];
        $this->agent = $config['agent'] ?? null;
        $this->dependencies = $config['dependencies'] ?? [];
    }
}
