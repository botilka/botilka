<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB\Initializer;

use Botilka\Infrastructure\StoreInitializer;
use Botilka\Tests\AbstractKernelTestCase;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use PHPUnit\Framework\Attributes\Group;

abstract class AbstractMongoDBStoreInitializerTest extends AbstractKernelTestCase
{
    /** @var StoreInitializer */
    protected $initializer;

    /** @var string */
    protected $database;

    /** @var string */
    protected $collectionName;

    /** @var string */
    protected $type;

    public function initializeProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    #[Group('functional')]
    public function testInitializeFunctional(): void
    {
        /** @var Client $client */
        $client = static::$container->get(Client::class);
        $client->selectDatabase($this->database)->dropCollection($this->collectionName);
        $initializer = $this->getInitializer($client);
        $initializer->initialize();
        self::assertTrue(true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Collection '{$this->collectionName}' already exists.");

        $initializer->initialize();
    }

    #[Group('functional')]
    public function testInitializeForceFunctional(): void
    {
        /** @var Client $client */
        $client = static::$container->get(Client::class);
        $client->selectDatabase($this->database)->dropCollection($this->collectionName);
        $initializer = $this->getInitializer($client);
        $initializer->initialize();
        $initializer->initialize(true);
        $initializer->initialize(true);
        self::assertTrue(true);
    }

    public function testInitializeCommandException(): void
    {
        $database = $this->createMock(Database::class);
        $database->expects(self::once())->method('createCollection')
            ->willThrowException(new \MongoDB\Driver\Exception\CommandException("Collection '{$this->collectionName}' already exists."))
        ;

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('selectDatabase')
            ->willReturn($database)
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Collection '{$this->collectionName}' already exists.");

        $initializer = $this->getInitializer($client);
        $initializer->initialize();
    }

    public function testGetType(): void
    {
        $client = $this->createMock(Client::class);
        $initializer = $this->getInitializer($client);

        self::assertSame($this->type, $initializer->getType());
    }

    abstract protected function getInitializer(Client $client): StoreInitializer;

    protected function assertInitialize(bool $force, array $createIndexParams): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->expects(self::once())
            ->method('createIndex')
            ->with($createIndexParams, ['unique' => true])
        ;

        $database = $this->createMock(Database::class);
        $database->expects(self::once())->method('selectCollection')
            ->with($this->collectionName)->willReturn($collection)
        ;
        $database->expects($force ? self::once() : self::never())->method('dropCollection')
            ->with($this->collectionName)->willReturn($collection)
        ;

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('selectDatabase')
            ->willReturn($database)
        ;

        $initializer = $this->getInitializer($client);
        $initializer->initialize($force);
    }
}
