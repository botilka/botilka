<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB\Initializer;

use Botilka\Infrastructure\MongoDB\Initializer\SnapshotStoreMongoDBInitializer;
use Botilka\Infrastructure\StoreInitializer;
use MongoDB\Client;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
final class SnapshotStoreMongoDBInitializerTest extends AbstractMongoDBStoreInitializerTest
{
    protected $type = 'snapshot';

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::$container;
        /** @var string $database */
        $database = $container->getParameter('botilka.mongodb.db').'_test';
        /** @var string $collectionName */
        $collectionName = $container->getParameter('botilka.snapshot_store.collection').'_test';
        $this->database = $database;
        $this->collectionName = $collectionName;
    }

    #[DataProvider('initializeProvider')]
    public function testInitialize(bool $force): void
    {
        $this->assertInitialize($force, ['id' => 1]);
    }

    protected function getInitializer(Client $client): StoreInitializer
    {
        return new SnapshotStoreMongoDBInitializer($client, $this->database, $this->collectionName);
    }
}
