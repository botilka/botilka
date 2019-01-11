<?php

declare(strict_types=1);

namespace Botilka\Snapshot;

use Botilka\Domain\EventSourcedAggregateRoot;

interface SnapshotStore
{
    /**
     * @throws SnapshotNotFoundException
     */
    public function load(string $id): EventSourcedAggregateRoot;

    public function snapshot(EventSourcedAggregateRoot $aggregateRoot): void;
}
