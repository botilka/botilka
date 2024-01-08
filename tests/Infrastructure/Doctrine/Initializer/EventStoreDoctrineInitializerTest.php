<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine\Initializer;

use Botilka\Infrastructure\Doctrine\Initializer\EventStoreDoctrineInitializer;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(EventStoreDoctrineInitializer::class)]
final class EventStoreDoctrineInitializerTest extends AbstractDoctrineInitializerTest
{
    protected $type = 'event';

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::$container;

        $this->tableName = $container->getParameter('botilka.event_store.collection').'_test';

        $this->resetStore();
        $this->initializer = new EventStoreDoctrineInitializer($this->connection, $this->tableName);
    }
}
