<?php

declare(strict_types=1);

namespace Botilka\Snapshot\Strategist;

use Botilka\Domain\EventSourcedAggregateRoot;

interface SnapshotStrategist
{
    public function mustSnapshot(EventSourcedAggregateRoot $aggregateRoot): bool;
}
