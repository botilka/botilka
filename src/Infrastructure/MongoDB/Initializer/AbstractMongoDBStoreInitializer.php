<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\MongoDB\Initializer;

use MongoDB\Client;

abstract class AbstractMongoDBStoreInitializer
{
    private $client;
    private $database;
    private $collection;

    public function __construct(Client $client, string $database, string $collection)
    {
        $this->client = $client;
        $this->database = $database;
        $this->collection = $collection;
    }

    protected function doInitialize(array $uniqueIndexKeys, bool $force): void
    {
        $database = $this->client->selectDatabase($this->database);

        if (true === $force) {
            $database->dropCollection($this->collection);
        }

        try {
            $database->createCollection($this->collection);
        } catch (\MongoDB\Driver\Exception\CommandException $e) {
            throw new \RuntimeException("Collection '{$this->collection}' already exists.");
        }

        $database->selectCollection($this->collection)->createIndex($uniqueIndexKeys, ['unique' => true]);
    }
}
