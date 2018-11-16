<?php

declare(strict_types=1);

namespace Botilka\EventStore;

interface EventStoreManager
{
    /**
     * @return ManagedEvent[]
     */
    public function load(string $id, ?int $from = null, ?int $to = null): array;

    /**
     * Gets all the distinct aggregates root id.
     *
     * @return string[]
     */
    public function getAggregateRootIds(): array;
}
