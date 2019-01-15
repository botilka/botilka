<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB\Initializer;

use Botilka\Infrastructure\MongoDB\Initializer\EventStoreMongoDBInitializer;
use Botilka\Infrastructure\StoreInitializer;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

final class EventStoreMongoDBInitializerTest extends AbstractMongoDBStoreInitializerTest
{
    protected $type = 'event';

    protected function setUp()
    {
        /** @var string $database */
        $database = \getenv('MONGODB_DB').'_test';
        /** @var string $collection */
        $collection = \getenv('MONGODB_COLLECTION').'_test';
        $this->database = $database;
        $this->collection = $collection;
    }

    /** @dataProvider initializeProvider */
    public function testInitialize(bool $force): void
    {
        $this->assertInitialize($force, ['id' => 1, 'playhead' => 1]);
    }

    protected function getInitializer(Client $client): StoreInitializer
    {
        return new EventStoreMongoDBInitializer($client, $this->database, $this->collection);
    }
}
