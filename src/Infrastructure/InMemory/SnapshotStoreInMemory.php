<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\InMemory;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Snapshot\SnapshotNotFoundException;
use Botilka\Snapshot\SnapshotStore;

final class SnapshotStoreInMemory implements SnapshotStore
{
    /**
     * @var array<string, EventSourcedAggregateRoot>
     */
    private array $store = [];

    public function load(string $id): EventSourcedAggregateRoot
    {
        if (null === $aggregateRoot = $this->store[$id] ?? null) {
            throw new SnapshotNotFoundException();
        }

        return $aggregateRoot;
    }

    public function snapshot(EventSourcedAggregateRoot $aggregateRoot): void
    {
        $this->store[$aggregateRoot->getAggregateRootId()] = $aggregateRoot;
    }
}
