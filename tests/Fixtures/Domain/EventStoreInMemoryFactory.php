<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Domain;

use Botilka\Infrastructure\InMemory\EventStoreInMemory;

final class EventStoreInMemoryFactory
{
    public static function create(): EventStoreInMemory
    {
        $eventStore = new EventStoreInMemory();

        $rules = [
            [10, 2, 'foo', 'FooBar\\Domain'],
            [5, 3, 'bar', 'FooBar\\Domain'],
            [10, 4, 'faz', 'FazBaz\\Domain'],
            [5, 1, 'baz', 'FazBaz\\Domain'],
        ];

        foreach ($rules as [$count, $factor, $id, $domain]) {
            for ($i = 0; $i < $count; ++$i) {
                $event = new StubEvent($i * $factor);
                $eventStore->append($id, $i, StubEvent::class, $event, null, new \DateTimeImmutable('2018-11-14 19:42:'.($i * $factor).'.1234'), $domain);
            }
        }

        return $eventStore;
    }
}
