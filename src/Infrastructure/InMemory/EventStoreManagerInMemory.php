<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\InMemory;

use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;

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
            $events[] = $storedEvent;
        }

        return $events;
    }

    public function loadByDomain(string $domain): iterable
    {
        $result = [];

        foreach ($this->eventStore->getStore() as $events) {
            /** @var ManagedEvent $event */
            foreach ($events as $event) {
                if ($event->getDomain() === $domain) {
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
            /** @var ManagedEvent $event */
            foreach ($events as $event) {
                $result[] = $event->getDomain();
            }
        }

        return \array_values(\array_unique($result));
    }

    public function getAggregateRootIds(): array
    {
        /** @var string[] $keys */
        $keys = \array_keys($this->eventStore->getStore());

        return $keys;
    }
}
