<?php

declare(strict_types=1);

namespace Botilka\EventStore;

interface EventStoreManager
{
    public const TARGET_DOMAIN = 'domain';
    public const TARGET_ID = 'id';
    public const TARGETS = [
        self::TARGET_DOMAIN,
        self::TARGET_ID,
    ];

    /**
     * @return ManagedEvent[]
     */
    public function loadByAggregateRootId(string $id, ?int $from = null, ?int $to = null): array;

    /**
     * @return ManagedEvent[]
     */
    public function loadByDomain(string $domain, ?int $from = null, ?int $to = null): array;

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
