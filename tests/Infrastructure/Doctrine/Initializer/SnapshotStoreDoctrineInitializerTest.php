<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine\Initializer;

use Botilka\Infrastructure\Doctrine\Initializer\SnapshotStoreDoctrineInitializer;

final class SnapshotStoreDoctrineInitializerTest extends AbstractDoctrineInitializerTest
{
    protected $type = 'snapshot';

    protected function setUp()
    {
        $this->tableName = \getenv('SNAPSHOT_STORE_COLLECTION').'_test';

        $this->resetStore();
        $this->initializer = new SnapshotStoreDoctrineInitializer($this->connection, $this->tableName);
    }
}
