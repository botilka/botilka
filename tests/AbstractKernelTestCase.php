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
use MongoDB\Collection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

    public static function setUpDoctrine(KernelInterface $kernel): void
    {
        $application = new DropDatabaseDoctrineCommand();
        $application->setContainer(self::$container);
        $application->run(new ArrayInput(['--force' => true]), new NullOutput());

        $application = new CreateDatabaseDoctrineCommand();
        $application->setContainer(self::$container);
        $application->run(new ArrayInput([]), new NullOutput());
    }

    protected static function setUpMongoDb(): void
    {
        if (null !== static::$container) {
            return;
        }

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

        $collection = self::getMongoDBCollection($container);

        $eventStore = new EventStoreMongoDB($collection, $normalizer, $denormalizer);
        foreach (['foo', 'bar'] as $id) {
            for ($i = 0; $i < ('foo' === $id ? 10 : 5); ++$i) {
                $eventStore->append($id, $i, StubEvent::class, new StubEvent($i * ('foo' === $id ? 2 : 3)), [$id => $i], new \DateTimeImmutable());
            }
        }
        static::assertInstanceOf(EventStore::class, $eventStore);
        static::$eventStore = $eventStore;
    }

    protected static function getMongoDBCollection(ContainerInterface $container): Collection
    {
        /** @var Client $client */
        $client = $container->get(Client::class);
        /** @var string $database */
        $database = \getenv('MONGODB_DB').'_test';
        /** @var string $collectionName */
        $collection = \getenv('MONGODB_COLLECTION').'_test';

        return $client->selectCollection($database, $collection);
    }
}
