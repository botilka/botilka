<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\InMemory;

use Botilka\EventStore\DefaultManagedEvent;
use Botilka\EventStore\EventStoreManager;

final class EventStoreManagerInMemory implements EventStoreManager
{
    private $eventStore;

    public function __construct(EventStoreInMemory $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function load(string $id, ?int $from = null, ?int $to = null): array
    {
        $store = $this->eventStore->getStore();
        $storedEvents = $store[$id];

        if (null !== $from) {
            $storedEvents = \array_slice($storedEvents, $from);
        }

        if (null !== $to) {
            $storedEvents = \array_slice($storedEvents, $to - $from + 1);
        }

        $events = [];
        foreach ($storedEvents as $storedEvent) {
            $events[] = new DefaultManagedEvent(
                $storedEvent['payload'],
                $storedEvent['playhead'],
                $storedEvent['metadata'],
                $storedEvent['recordedOn']
            );
        }

        return $events;
    }

    public function getAggregateRootIds(): array
    {
        return \array_keys($this->eventStore->getStore());
    }
}
