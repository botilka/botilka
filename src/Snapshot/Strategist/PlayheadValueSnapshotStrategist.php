<?php

declare(strict_types=1);

namespace Botilka\Snapshot\Strategist;

use Botilka\Domain\EventSourcedAggregateRoot;

final readonly class PlayheadValueSnapshotStrategist implements SnapshotStrategist
{
    public function __construct(
        private int $eachPlayhead = 5,
    ) {}

    public function getEachPlayhead(): int
    {
        return $this->eachPlayhead;
    }

    public function mustSnapshot(EventSourcedAggregateRoot $aggregateRoot): bool
    {
        return 0 === ($aggregateRoot->getPlayhead() + 1) % $this->eachPlayhead;
    }
}
