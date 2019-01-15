<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine\Initializer;

use Botilka\Infrastructure\StoreInitializer;
use Botilka\Tests\AbstractKernelTestCase;
use Botilka\Tests\Fixtures\Application\EventStore\DoctrineSetupTrait;
use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\RegistryInterface;

abstract class AbstractDoctrineInitializerTest extends AbstractKernelTestCase
{
    /** @var Connection */
    protected $connection;

    /** @var StoreInitializer */
    protected $initializer;

    /** @var string */
    protected $table;

    /** @var string */
    protected $type;

    use DoctrineSetupTrait;

    protected function resetStore(): void
    {
        $kernel = static::bootKernel();
        $this->setUpDatabase($kernel);
        $container = self::$container;

        /** @var RegistryInterface $registry */
        $registry = self::$container->get('doctrine');

        /** @var Connection $connection */
        $connection = $registry->getConnection();
        $connection->getConfiguration()->setSQLLogger(null);
        $connection->exec("DROP TABLE IF EXISTS {$this->table};");
        $this->connection = $connection;
    }

    /**
     * @group functional
     * @expectedException \RuntimeException
     */
    public function testInitialize(): void
    {
        $this->initializer->initialize();
        $this->assertTrue(true);

        $this->expectExceptionMessageRegExp('/Duplicate table:.*relation "'.$this->table.'" already exists/');

        $this->initializer->initialize();
    }

    /** @group functional */
    public function testInitializeForce(): void
    {
        $this->initializer->initialize();
        $this->initializer->initialize(true);
        $this->initializer->initialize(true);
        $this->assertTrue(true);
    }

    /**
     * @group functional
     */
    public function testGetType(): void
    {
        $this->assertSame($this->type, $this->initializer->getType());
    }
}
