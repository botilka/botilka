<?php

declare(strict_types=1);

namespace Botilka\EventStore;

interface EventStoreManager
{
    /**
     * @return ManagedEvent[]
     */
    public function loadByAggregateRootId(string $id, ?int $from = null, ?int $to = null): iterable;

    /**
     * @return ManagedEvent[]
     */
    public function loadByDomain(string $domain): iterable;

    /**
     * Gets all the distinct aggregates root id.
     *
     * @return string[]
     */
    public function getAggregateRootIds(): array;

    /**
     * Gets all the distinct domains.
     *
     * @return string[]
     */
    public function getDomains(): array;
}
