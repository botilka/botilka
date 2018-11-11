<?php

namespace Botilka\Infrastructure\MongoDB;

use Botilka\Application\EventStore\EventStoreInitializer;
use MongoDB\Client;

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

    public function initialize(): void
    {
        $database = $this->client->selectDatabase($this->database);
        $database->dropCollection($this->collection);
        $database->createCollection($this->collection);
        $database->selectCollection($this->collection)->createIndexes([['key' => ['id' => 1, 'playhead' => 1], 'unique' => true]]);
    }
}
