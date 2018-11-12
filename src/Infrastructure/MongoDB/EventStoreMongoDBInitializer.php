<?php

declare(strict_types=1);

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

    public function initialize(bool $force = false): void
    {
        $database = $this->client->selectDatabase($this->database);

        if (true === $force) {
            $database->dropCollection($this->collection);
        }

        try {
            $database->createCollection($this->collection);
        } catch (\Exception $e) {
            if ('MongoDB\\Driver\\Exception\\CommandException' === \get_class($e)) {
                throw new \RuntimeException("Collection '{$this->collection}' already exists.");
            }
            throw $e;
        }

        $database->selectCollection($this->collection)->createIndexes([['key' => ['id' => 1, 'playhead' => 1], 'unique' => true]]);
    }
}
