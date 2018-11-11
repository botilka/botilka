<?php

namespace Botilka\Infrastructure\MongoDB;

use Botilka\Application\EventStore\EventStoreInitializer;
use MongoDB\Client;
use MongoDB\Driver\Exception\CommandException;

final class EventStoreMongoDBInitializer implements EventStoreInitializer
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
        } catch (CommandException $e) {
            throw new \RuntimeException("Collection '{$this->collection}' already exists.");
        }

        $database->selectCollection($this->collection)->createIndexes([['key' => ['id' => 1, 'playhead' => 1], 'unique' => true]]);
    }
}
