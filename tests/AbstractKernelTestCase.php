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

    /** @var EventStore */
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

    protected function setUpMongoDb(): void
    {
        if (null !== static::$eventStore) {
            return;
        }

        static::bootKernel();
        $container = static::$container;

        /** @var Client $client */
        $client = $container->get(Client::class);
        /** @var string $database */
        $database = \getenv('MONGODB_DB').'_test';
        /** @var string $collection */
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
}
