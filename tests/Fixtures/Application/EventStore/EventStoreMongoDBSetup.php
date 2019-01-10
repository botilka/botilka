<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\EventStore;

use Botilka\EventStore\EventStore;
use Botilka\Infrastructure\MongoDB\EventStoreMongoDB;
use Botilka\Infrastructure\MongoDB\EventStoreMongoDBInitializer;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use MongoDB\Client;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

trait EventStoreMongoDBSetup
{
    private function setUpEventStore(): array
    {
        static::bootKernel();
        $container = static::$container;

        /** @var Client $client */
        $client = $container->get(Client::class);
        /** @var string $database */
        $database = \getenv('MONGODB_DB').'_test';
        /** @var string $collectionName */
        $collectionName = \getenv('MONGODB_COLLECTION').'_test';

        $initializer = new EventStoreMongoDBInitializer($client, $database, $collectionName);
        $initializer->initialize(true);

        $collection = $client->selectCollection($database, $collectionName);

        /** @var NormalizerInterface $normalizer */
        $normalizer = $container->get('serializer');
        /** @var DenormalizerInterface $denormalizer */
        $denormalizer = $container->get('serializer');

        $eventStore = new EventStoreMongoDB($collection, $normalizer, $denormalizer);
        $this->assertInstanceOf(EventStore::class, $eventStore);

        foreach (['foo', 'bar'] as $id) {
            for ($i = 0; $i < ('foo' === $id ? 10 : 5); ++$i) {
                $eventStore->append($id, $i, StubEvent::class, new StubEvent($i * ('foo' === $id ? 2 : 3)), [$id => $i], new \DateTimeImmutable(), 'FooBar\\Domain');
            }
        }
        foreach (['faz', 'baz'] as $id) {
            for ($i = 0; $i < ('faz' === $id ? 10 : 5); ++$i) {
                $eventStore->append($id, $i, StubEvent::class, new StubEvent($i * ('faz' === $id ? 4 : 5)), [$id => $i], new \DateTimeImmutable(), 'FazBaz\\Domain');
            }
        }

        return [$eventStore, $collection];
    }
}
