<?php

namespace Botilka\Tests\Infrastructure\Doctrine;

use Botilka\Infrastructure\Doctrine\EventStoreDoctrineInitializer;
use Botilka\Tests\AbstractKernelTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\RegistryInterface;

final class EventStoreDoctrineInitializerTest extends AbstractKernelTestCase
{
    /** @var EventStoreDoctrineInitializer */
    private $initializer;

    /** @var string */
    private $table;

    public function setUp()
    {
        $kernel = static::bootKernel();
        static::setUpDoctrine($kernel);
        $container = self::$container;

        $table = $this->table = \getenv('POSTGRES_TABLE').'_test';

        /** @var RegistryInterface $registry */
        $registry = self::$container->get('doctrine');

        /** @var Connection $connection */
        $connection = $registry->getConnection();
        $connection->getConfiguration()->setSQLLogger(null);
        $connection->exec("DROP TABLE IF EXISTS $table;");

        $this->initializer = new EventStoreDoctrineInitializer($connection, $table);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Duplicate table:.*relation "event_store_test" already exists/
     */
    public function testInitialize(): void
    {
        $this->initializer->initialize();
        $this->assertTrue(true);

        $this->initializer->initialize();
    }

    public function testInitializeForce(): void
    {
        $this->initializer->initialize();
        $this->initializer->initialize(true);
        $this->initializer->initialize(true);
        $this->assertTrue(true);
    }
}
