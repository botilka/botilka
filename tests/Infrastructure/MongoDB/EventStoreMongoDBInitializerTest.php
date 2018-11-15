<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\Infrastructure\MongoDB\EventStoreMongoDBInitializer;
use Botilka\Tests\AbstractKernelTestCase;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

final class EventStoreMongoDBInitializerTest extends AbstractKernelTestCase
{
    /** @var EventStoreMongoDBInitializer */
    private $initializer;

    /** @var string */
    private $database;

    /** @var string */
    private $collection;

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
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('createIndex')
            ->with(['id' => 1, 'playhead' => 1], ['unique' => true]);

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
     * @group functional
     * @expectedException \RuntimeException
     */
    public function testInitializeFunctional(): void
    {
        static::bootKernel();
        /** @var Client $client */
        $client = static::$container->get(Client::class);
        $client->selectDatabase($this->database)->dropCollection($this->collection);
        $initializer = new EventStoreMongoDBInitializer($client, $this->database, $this->collection);
        $initializer->initialize();
        $this->assertTrue(true);

        $this->expectExceptionMessage("Collection '{$this->collection}' already exists.");
        $initializer->initialize();
    }

    /** @group functional */
    public function testInitializeForceFunctional(): void
    {
        static::bootKernel();
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
