<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\Infrastructure\MongoDB\EventStoreMongoDBInitializer;
use Botilka\Tests\AbstractKernelTestCase;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

if (!\class_exists('MongoDB\\Driver\\Exception\\CommandException')) {
}

final class EventStoreMongoDBInitializerTest extends AbstractKernelTestCase
{
    /** @var EventStoreMongoDBInitializer */
    private $initializer;

    /** @var string */
    private $database;

    /** @var string */
    private $collection;

    public function setUp()
    {
        static::bootKernel();
        $container = static::$container;

        $this->database = \getenv('MONGODB_DB').'_test';
        $this->collection = $this->collection = \getenv('MONGODB_COLLECTION').'_test';
    }

    /** @dataProvider initializeProvider */
    public function testInitialize(bool $force): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('createIndexes')
            ->with([['key' => ['id' => 1, 'playhead' => 1], 'unique' => true]]);

        $database = $this->createMock(Database::class);
        $database->expects($this->once())->method('selectCollection')
            ->with($this->collection)->willReturn($collection);
        $database->expects($force ? $this->once() : $this->never())->method('dropCollection')
            ->with($this->collection)->willReturn($collection);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('selectDatabase')
            ->willReturn($database);

        $initializer = new EventStoreMongoDBInitializer($client, $this->database, $this->collection);
        $initializer->initialize($force);
    }

    public function initializeProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @group functionnal
     * @expectedException \RuntimeException
     */
    public function testInitializeFunctionnal(): void
    {
        /** @var Client $client */
        $client = static::$container->get(Client::class);
        $client->selectDatabase($this->database)->dropCollection($this->collection);
        $initializer = new EventStoreMongoDBInitializer($client, $this->database, $this->collection);
        $initializer->initialize();
        $this->assertTrue(true);

        $this->expectExceptionMessage("Collection '{$this->collection}' already exists.");
        $initializer->initialize();
    }

    /** @group functionnal */
    public function testInitializeForceFunctionnal(): void
    {
        /** @var Client $client */
        $client = static::$container->get(Client::class);
        $client->selectDatabase($this->database)->dropCollection($this->collection);
        $initializer = new EventStoreMongoDBInitializer($client, $this->database, $this->collection);
        $initializer->initialize();
        $initializer->initialize(true);
        $initializer->initialize(true);
        $this->assertTrue(true);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInitializeCommandException(): void
    {
        $database = $this->createMock(Database::class);
        $database->expects($this->once())->method('createCollection')
            ->willThrowException(new \MongoDB\Driver\Exception\CommandException("Collection '{$this->collection}' already exists."));

        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('selectDatabase')
            ->willReturn($database);

        $this->expectExceptionMessage("Collection '{$this->collection}' already exists.");

        $initializer = new EventStoreMongoDBInitializer($client, $this->database, $this->collection);
        $initializer->initialize();
    }
}
