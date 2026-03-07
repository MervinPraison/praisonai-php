<?php

declare(strict_types=1);

namespace PraisonAI;

use OpenAI;
use OpenAI\Client;

/**
 * PraisonAI - Main factory class for the PraisonAI PHP framework.
 *
 * Provides convenience methods to create OpenAI clients and configure defaults.
 */
class PraisonAI
{
    private static string $defaultModel = 'gpt-4o';

    /**
     * Create an OpenAI client instance.
     */
    public static function client(?string $apiKey = null): Client
    {
        $apiKey = $apiKey ?? getenv('OPENAI_API_KEY') ?: '';

        return OpenAI::client($apiKey);
    }

    /**
     * Get the default model name.
     */
    public static function getDefaultModel(): string
    {
        return self::$defaultModel;
    }

    /**
     * Set the default model name.
     */
    public static function setDefaultModel(string $model): void
    {
        self::$defaultModel = $model;
    }
}
