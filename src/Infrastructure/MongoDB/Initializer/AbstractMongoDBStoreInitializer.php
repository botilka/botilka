<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\MongoDB\Initializer;

use MongoDB\Client;

abstract readonly class AbstractMongoDBStoreInitializer
{
    public function __construct(
        private Client $client,
        private string $database,
        private string $collectionName,
    ) {
        $this->client = $client;
    }

    /**
     * @param array<string, int> $uniqueIndexKeys
     */
    protected function doInitialize(array $uniqueIndexKeys, bool $force): void
    {
        $database = $this->client->selectDatabase($this->database);

        if ($force) {
            $database->dropCollection($this->collectionName);
        }

        try {
            $database->createCollection($this->collectionName);
        } catch (\MongoDB\Driver\Exception\CommandException) {
            throw new \RuntimeException("Collection '{$this->collectionName}' already exists.");
        }

        $database->selectCollection($this->collectionName)->createIndex($uniqueIndexKeys, ['unique' => true]);
    }
}
