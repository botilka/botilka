<?php

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Botilka\Infrastructure\MongoDB\EventStoreMongoDB;
use Botilka\Infrastructure\MongoDB\EventStoreMongoDBInitializer;
use Botilka\Tests\AbstractKernelTestCase;
use Botilka\Tests\app\AppKernel;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\Cursor;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventStoreMongoDBTest extends AbstractKernelTestCase
{
    /** @var EventStoreMongoDB */
    private static $eventStore;

    protected function setUp()
    {
        if (null !== static::$eventStore) {
            return;
        }

        static::bootKernel();
        $container = static::$container;

        /** @var Client $client */
        $client = $container->get(Client::class);
        $database = \getenv('MONGODB_DB').'_test';
        $collection = \getenv('MONGODB_COLLECTION').'_test';

        $initializer = new EventStoreMongoDBInitializer($client, $database, $collection);
        $initializer->initialize(true);

        /** @var NormalizerInterface $normalizer */
        $normalizer = $container->get('serializer');
        /** @var DenormalizerInterface $denormalizer */
        $denormalizer = $container->get('serializer');

        $eventStore = new EventStoreMongoDB($client->selectCollection($database, $collection), $normalizer, $denormalizer);
        $eventStore->append('bar', 1, StubEvent::class, new StubEvent(42), null, new \DateTimeImmutable());
        for ($i = 0; $i < 5; ++$i) {
            $eventStore->append('foo', $i, StubEvent::class, new StubEvent($i * 100), null, new \DateTimeImmutable());
        }
        $this->assertInstanceOf(EventStore::class, $eventStore);
        static::$eventStore = $eventStore;
    }

    public function testLoad()
    {
        $this->assertCount(5, static::$eventStore->load('foo'));
        $this->assertCount(1, static::$eventStore->load('bar'));
    }

    public function testLoadFromPlayheadToPlayhead()
    {
        $this->assertCount(1, static::$eventStore->loadFromPlayheadToPlayhead('foo', 2, 3));
        $this->assertCount(1, static::$eventStore->loadFromPlayheadToPlayhead('foo', 4, 10));
    }

    public function testLoadFromPlayhead()
    {
        $this->assertCount(3, static::$eventStore->loadFromPlayhead('foo', 2));
        $this->assertCount(1, static::$eventStore->loadFromPlayhead('foo', 4));
    }

    /**
     * @expectedException \Botilka\EventStore\EventStoreConcurrencyException
     * @expectedExceptionMessage Duplicate storage of event "Botilka\Tests\Fixtures\Domain\StubEvent" on aggregate "bar" with playhead 1.
     */
    public function testAppendBulkWriteException()
    {
        static::$eventStore->append('bar', 1, StubEvent::class, new StubEvent(42), null, new \DateTimeImmutable());
    }
}
