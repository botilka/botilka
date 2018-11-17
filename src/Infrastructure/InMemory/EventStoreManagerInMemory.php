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

    public function loadByAggregateRootId(string $id, ?int $from = null, ?int $to = null): array
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

    public function loadByDomain(string $domain, ?int $from = null, ?int $to = null): array
    {
        throw new \BadMethodCallException('not implemented');
    }

    public function getDomains(): array
    {
        throw new \BadMethodCallException('not implemented');
    }

    public function getAggregateRootIds(): array
    {
        return \array_keys($this->eventStore->getStore());
    }
}
