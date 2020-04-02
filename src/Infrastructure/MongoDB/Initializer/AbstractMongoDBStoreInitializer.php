<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\MongoDB\Initializer;

use MongoDB\Client;

abstract class AbstractMongoDBStoreInitializer
{
    private $client;
    private $database;
    private $collectionName;

    public function __construct(Client $client, string $database, string $collectionName)
    {
        $this->client = $client;
        $this->database = $database;
        $this->collectionName = $collectionName;
    }

    /**
     * @param array<string, int> $uniqueIndexKeys
     */
    protected function doInitialize(array $uniqueIndexKeys, bool $force): void
    {
        $database = $this->client->selectDatabase($this->database);

        if (true === $force) {
            $database->dropCollection($this->collectionName);
        }

        try {
            $database->createCollection($this->collectionName);
        } catch (\MongoDB\Driver\Exception\CommandException $e) {
            throw new \RuntimeException("Collection '{$this->collectionName}' already exists.");
        }

        $database->selectCollection($this->collectionName)->createIndex($uniqueIndexKeys, ['unique' => true]);
    }
}
