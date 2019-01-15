<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB\Initializer;

use Botilka\Infrastructure\StoreInitializer;
use Botilka\Tests\AbstractKernelTestCase;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

abstract class AbstractMongoDBStoreInitializerTest extends AbstractKernelTestCase
{
    /** @var StoreInitializer */
    protected $initializer;

    /** @var string */
    protected $database;

    /** @var string */
    protected $collection;

    /** @var string */
    protected $type;

    abstract protected function getInitializer(Client $client): StoreInitializer;

    public function initializeProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    protected function assertInitialize(bool $force, array $createIndexParams): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('createIndex')
            ->with($createIndexParams, ['unique' => true]);

        $database = $this->createMock(Database::class);
        $database->expects($this->once())->method('selectCollection')
            ->with($this->collection)->willReturn($collection);
        $database->expects($force ? $this->once() : $this->never())->method('dropCollection')
            ->with($this->collection)->willReturn($collection);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('selectDatabase')
            ->willReturn($database);

        $initializer = $this->getInitializer($client);
        $initializer->initialize($force);
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
        $initializer = $this->getInitializer($client);
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
        $initializer = $this->getInitializer($client);
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

        $initializer = $this->getInitializer($client);
        $initializer->initialize();
    }

    public function testGetType()
    {
        $client = $this->createMock(Client::class);
        $initializer = $this->getInitializer($client);

        $this->assertSame($this->type, $initializer->getType());
    }
}
