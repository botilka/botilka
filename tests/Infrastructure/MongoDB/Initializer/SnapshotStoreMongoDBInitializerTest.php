<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB\Initializer;

use Botilka\Infrastructure\MongoDB\Initializer\SnapshotStoreMongoDBInitializer;
use Botilka\Infrastructure\StoreInitializer;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

final class SnapshotStoreMongoDBInitializerTest extends AbstractMongoDBStoreInitializerTest
{
    protected $type = 'snapshot';

    protected function setUp()
    {
        /** @var string $database */
        $database = \getenv('MONGODB_DB').'_test';
        /** @var string $collection */
        $collection = \getenv('SNAPSHOT_STORE_COLLECTION').'_test';
        $this->database = $database;
        $this->collection = $collection;
    }

    /** @dataProvider initializeProvider */
    public function testInitialize(bool $force): void
    {
        $this->assertInitialize($force, ['id' => 1]);
    }

    protected function getInitializer(Client $client): StoreInitializer
    {
        return new SnapshotStoreMongoDBInitializer($client, $this->database, $this->collection);
    }
}
