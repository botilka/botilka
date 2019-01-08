<?php

declare(strict_types=1);

namespace Botilka\Snapshot\Strategist;

use Botilka\Domain\EventSourcedAggregateRoot;

final class PlayheadValueSnapshotStrategist implements SnapshotStrategist
{
    private $eachPlayhead = 50;

    public function __construct(int $eachPlayhead)
    {
        $this->eachPlayhead = $eachPlayhead;
    }

    public function mustSnapshot(EventSourcedAggregateRoot $aggregateRoot): bool
    {
        return 0 === ($aggregateRoot->getPlayhead() + 1) % $this->eachPlayhead;
    }
}
