<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine\Initializer;

use Botilka\Infrastructure\Doctrine\Initializer\SnapshotStoreDoctrineInitializer;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(SnapshotStoreDoctrineInitializer::class)]
final class SnapshotStoreDoctrineInitializerTest extends AbstractDoctrineInitializerTest
{
    protected $type = 'snapshot';

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::$container;

        $this->tableName = $container->getParameter('botilka.snapshot_store.collection').'_test';

        $this->resetStore();
        $this->initializer = new SnapshotStoreDoctrineInitializer($this->connection, $this->tableName);
    }
}
