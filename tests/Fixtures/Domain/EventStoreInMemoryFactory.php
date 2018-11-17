<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Domain;

use Botilka\Infrastructure\InMemory\EventStoreInMemory;

final class EventStoreInMemoryFactory
{
    public static function create(): EventStoreInMemory
    {
        $eventStore = new EventStoreInMemory();

        for ($i = 0; $i < 10; ++$i) {
            $event = new StubEvent($i * 2);
            $eventStore->append('foo', $i, StubEvent::class, $event, null, new \DateTimeImmutable('2018-11-14 19:42:'.($i * 2).'.1234'), 'Foo\\Domain');
        }

        for ($i = 0; $i < 5; ++$i) {
            $event = new StubEvent($i * 3);
            $eventStore->append('bar', $i, StubEvent::class, $event, null, new \DateTimeImmutable('2018-11-14 19:42:'.($i * 3).'.4321'), 'Foo\\Domain');
        }

        return $eventStore;
    }
}
