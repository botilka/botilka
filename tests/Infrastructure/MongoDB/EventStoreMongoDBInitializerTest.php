<?php

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\Infrastructure\MongoDB\EventStoreMongoDBInitializer;
use Botilka\Tests\AbstractKernelTestCase;
use MongoDB\Client;

final class EventStoreMongoDBInitializerTest extends AbstractKernelTestCase
{
    /** @var EventStoreMongoDBInitializer */
    private $initializer;

    /** @var string */
    private $collection;

    public function setUp()
    {
        static::bootKernel();
        $container = static::$container;

        /** @var Client $client */
        $client = $container->get(Client::class);
        $database = \getenv('MONGODB_DB').'_test';
        $collection = $this->collection = \getenv('MONGODB_COLLECTION').'_test';

        $client->selectDatabase($database)->dropCollection($collection);

        $this->initializer = new EventStoreMongoDBInitializer($client, $database, $collection);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInitialize(): void
    {
        $this->initializer->initialize();
        $this->assertTrue(true);

        $this->expectExceptionMessage("Collection '{$this->collection}' already exists.");
        $this->initializer->initialize();
    }

    public function testInitializeForce(): void
    {
        $this->initializer->initialize();
        $this->initializer->initialize(true);
        $this->initializer->initialize(true);
        $this->assertTrue(true);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageReg Foo message.
     */
    public function testInitializeUnknowException(): void
    {
        $database = \getenv('MONGODB_DB').'_test';
        $collection = $this->collection = \getenv('MONGODB_COLLECTION').'_test';

        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('selectDatabase')
            ->willThrowException(new \Exception('Foo message.'));

        $initializer = new EventStoreMongoDBInitializer($client, $database, $collection);
        $initializer->initialize();
    }
}
