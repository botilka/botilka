<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\MongoDB\Initializer;

use Botilka\Infrastructure\StoreInitializer;
use MongoDB\Client;

final class EventStoreMongoDBInitializer implements StoreInitializer
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

    public function initialize(bool $force = false): void
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

        $database->selectCollection($this->collection)->createIndex(['id' => 1, 'playhead' => 1], ['unique' => true]);
    }

    public function getType(): string
    {
        return StoreInitializer::TYPE_EVENT_STORE;
    }
}
