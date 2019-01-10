<?php

declare(strict_types=1);

namespace Botilka\Tests;

use Botilka\EventStore\EventStore;
use Botilka\Infrastructure\MongoDB\EventStoreMongoDB;
use Botilka\Infrastructure\MongoDB\EventStoreMongoDBInitializer;
use Botilka\Tests\app\AppKernel;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use MongoDB\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractKernelTestCase extends KernelTestCase
{
    protected static $class = AppKernel::class;

    /** @var ?EventStore */
    protected static $eventStore;

    public static function bootKernel(array $options = [])
    {
        return parent::bootKernel($options + ['environment' => 'test']);
    }

    public static function setUpDoctrineEventStore(KernelInterface $kernel): void
    {
        $application = new DropDatabaseDoctrineCommand();
        $application->setContainer(self::$container);
        $application->run(new ArrayInput(['--force' => true]), new NullOutput());

        $application = new CreateDatabaseDoctrineCommand();
        $application->setContainer(self::$container);
        $application->run(new ArrayInput([]), new NullOutput());
    }

    protected static function setUpMongoDbEventStore(): array
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

        /** @var NormalizerInterface $normalizer */
        $normalizer = $container->get('serializer');
        /** @var DenormalizerInterface $denormalizer */
        $denormalizer = $container->get('serializer');

        $collection = $client->selectCollection($database, $collectionName);

        $eventStore = new EventStoreMongoDB($collection, $normalizer, $denormalizer);
        static::assertInstanceOf(EventStore::class, $eventStore);

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
