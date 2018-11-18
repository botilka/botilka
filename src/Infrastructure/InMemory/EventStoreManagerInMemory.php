<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\InMemory;

use Botilka\EventStore\ManagedEvent;
use Botilka\EventStore\EventStoreManager;

final class EventStoreManagerInMemory implements EventStoreManager
{
    private $eventStore;

    public function __construct(EventStoreInMemory $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function loadByAggregateRootId(string $id, ?int $from = null, ?int $to = null): iterable
    {
        $store = $this->eventStore->getStore();
        $storedEvents = \array_slice($store[$id], null !== $from ? $from : 0, null !== $to ? $to - $from : null);

        $events = [];
        foreach ($storedEvents as $storedEvent) {
            $events[] = new ManagedEvent(
                $storedEvent['id'],
                $storedEvent['payload'],
                $storedEvent['playhead'],
                $storedEvent['metadata'],
                $storedEvent['recordedOn'],
                $storedEvent['domain']
            );
        }

        return $events;
    }

    public function loadByDomain(string $domain): iterable
    {
        $result = [];

        foreach ($this->eventStore->getStore() as $id => $events) {
            foreach ($events as $event) {
                if ($event['domain'] === $domain) {
                    $result[] = $event;
                }
            }
        }

        return $result;
    }

    public function getDomains(): array
    {
        $result = [];

        foreach ($this->eventStore->getStore() as $id => $events) {
            foreach ($events as $event) {
                $result[] = $event['domain'];
            }
        }

        return \array_values(\array_unique($result));
    }

    public function getAggregateRootIds(): array
    {
        return \array_keys($this->eventStore->getStore());
    }
}
