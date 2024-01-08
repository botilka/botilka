<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\InMemory;

use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;

final readonly class EventStoreManagerInMemory implements EventStoreManager
{
    public function __construct(private EventStoreInMemory $eventStore) {}

    public function loadByAggregateRootId(string $id, int $from = null, int $to = null): iterable
    {
        $store = $this->eventStore->getStore();

        return \array_slice($store[$id], $from ?? 0, null !== $to ? $to - $from : null);
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

        foreach ($this->eventStore->getStore() as $events) {
            /** @var ManagedEvent $event */
            foreach ($events as $event) {
                $result[] = $event->getDomain();
            }
        }

        return array_values(array_unique($result));
    }

    public function getAggregateRootIds(): array
    {
        return array_keys($this->eventStore->getStore());
    }
}
