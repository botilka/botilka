<?php

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\Infrastructure\MongoDB\EventStoreMongoDBInitializer;
use Botilka\Tests\AbstractKernelTestCase;
use Botilka\Tests\app\AppKernel;
use MongoDB\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EventStoreMongoDBInitializerTest extends AbstractKernelTestCase
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

        $this->initializer  = new EventStoreMongoDBInitializer($client, $database, $collection);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInitialize()
    {
        $this->initializer->initialize();
        $this->assertTrue(true);

        $this->expectExceptionMessage("Collection '{$this->collection}' already exists.");
        $this->initializer->initialize();
    }

    public function testInitializeForce()
    {
        $this->initializer->initialize();
        $this->initializer->initialize(true);
        $this->initializer->initialize(true);
        $this->assertTrue(true);
    }
}