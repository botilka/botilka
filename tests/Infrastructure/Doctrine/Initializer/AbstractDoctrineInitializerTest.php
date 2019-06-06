<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine\Initializer;

use Botilka\Infrastructure\StoreInitializer;
use Botilka\Tests\AbstractKernelTestCase;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractDoctrineInitializerTest extends AbstractKernelTestCase
{
    /** @var Connection */
    protected $connection;

    /** @var StoreInitializer */
    protected $initializer;

    /** @var string */
    protected $tableName;

    /** @var string */
    protected $type;

    private $needDropTable = false;

    protected function resetStore(): void
    {
        $this->setUpDatabase(static::$kernel);
        $container = self::$container;

        /** @var RegistryInterface $registry */
        $registry = self::$container->get('doctrine');

        /** @var Connection $connection */
        $connection = $registry->getConnection();
        $connection->getConfiguration()->setSQLLogger(null);
        $this->connection = $connection;
        $this->needDropTable = true;
    }

    private function setUpDatabase(KernelInterface $kernel): void
    {
        if ('true' !== \getenv('BOTILKA_TEST_FORCE_RECREATE_DB')) {
            return;
        }

        /** @var ManagerRegistry $doctrine */
        $doctrine = self::$container->get('doctrine');
        $application = new DropDatabaseDoctrineCommand($doctrine);
        $application->run(new ArrayInput(['--force' => true]), new NullOutput());

        $application = new CreateDatabaseDoctrineCommand($doctrine);
        $application->run(new ArrayInput([]), new NullOutput());
    }

    protected function tearDown(): void
    {
        if ($this->needDropTable) {
            $container = self::$container;

            /** @var RegistryInterface $registry */
            $registry = self::$container->get('doctrine');

            /** @var Connection $connection */
            $connection = $registry->getConnection();
            $connection->exec("DROP TABLE IF EXISTS {$this->tableName};");
        }
    }

    /**
     * @group functional
     * @expectedException \RuntimeException
     */
    public function testInitialize(): void
    {
        $this->initializer->initialize();

        $this->expectExceptionMessageRegExp('/Duplicate table:.*relation "'.$this->tableName.'" already exists/');

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
