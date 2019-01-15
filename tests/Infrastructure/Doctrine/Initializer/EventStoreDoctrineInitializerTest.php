<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine\Initializer;

use Botilka\Infrastructure\Doctrine\Initializer\EventStoreDoctrineInitializer;

final class EventStoreDoctrineInitializerTest extends AbstractDoctrineInitializerTest
{
    protected $type = 'event';

    protected function setUp()
    {
        $this->table = \getenv('POSTGRES_TABLE').'_test';

        $this->resetStore();
        $this->initializer = new EventStoreDoctrineInitializer($this->connection, $this->table);
    }
}
