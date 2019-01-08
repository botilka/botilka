<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\InMemory;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Snapshot\SnapshotStore;

final class SnapshotStoreInMemory implements SnapshotStore
{
    private $store = [];

    public function load(string $id): ?EventSourcedAggregateRoot
    {
        return $this->store[$id] ?? null;
    }

    public function snapshot(EventSourcedAggregateRoot $aggregateRoot): void
    {
        $this->store[$id] = $aggregateRoot;
    }
}
