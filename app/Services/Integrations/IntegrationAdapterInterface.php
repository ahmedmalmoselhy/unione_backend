<?php

namespace App\Services\Integrations;

/**
 * Integration Adapter Interface
 * All third-party integrations should implement this interface.
 */
interface IntegrationAdapterInterface
{
    /**
     * Initialize the integration with configuration.
     */
    public function initialize(array $config): void;

    /**
     * Test the integration connection.
     */
    public function testConnection(): bool;

    /**
     * Sync data to the integration.
     */
    public function sync(array $data): bool;

    /**
     * Get integration status.
     */
    public function getStatus(): array;
}
