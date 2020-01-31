<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine\Initializer;

use Botilka\Infrastructure\Doctrine\Initializer\SnapshotStoreDoctrineInitializer;

final class SnapshotStoreDoctrineInitializerTest extends AbstractDoctrineInitializerTest
{
    protected $type = 'snapshot';

    protected function setUp(): void
    {
        $kernel = static::bootKernel();
        $container = static::$container;

        $this->tableName = $container->getParameter('botilka.snapshot_store.collection').'_test';

        $this->resetStore();
        $this->initializer = new SnapshotStoreDoctrineInitializer($this->connection, $this->tableName);
    }
}
