<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine;

use Botilka\Infrastructure\Doctrine\EventStoreDoctrineInitializer;
use Botilka\Tests\AbstractKernelTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\RegistryInterface;

final class EventStoreDoctrineInitializerTest extends AbstractKernelTestCase
{
    /** @var EventStoreDoctrineInitializer */
    private $initializer;

    private function resetEventStore(): void
    {
        $kernel = static::bootKernel();
        static::setUpDoctrine($kernel);
        $container = self::$container;

        /** @var string $table */
        $table = \getenv('POSTGRES_TABLE').'_test';

        /** @var RegistryInterface $registry */
        $registry = self::$container->get('doctrine');

        /** @var Connection $connection */
        $connection = $registry->getConnection();
        $connection->getConfiguration()->setSQLLogger(null);
        $connection->exec("DROP TABLE IF EXISTS {$table};");

        $this->initializer = new EventStoreDoctrineInitializer($connection, $table);
    }

    /**
     * @group functionnal
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Duplicate table:.*relation "event_store_test" already exists/
     */
    public function testInitialize(): void
    {
        $this->resetEventStore();
        $this->initializer->initialize();
        $this->assertTrue(true);

        $this->initializer->initialize();
    }

    /** @group functionnal */
    public function testInitializeForce(): void
    {
        $this->resetEventStore();
        $this->initializer->initialize();
        $this->initializer->initialize(true);
        $this->initializer->initialize(true);
        $this->assertTrue(true);
    }
}
