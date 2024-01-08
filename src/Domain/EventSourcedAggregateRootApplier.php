<?php

declare(strict_types=1);

namespace Botilka\Domain;

use Botilka\Event\Event;

trait EventSourcedAggregateRootApplier
{
    private int $playhead = -1;

    public function apply(Event $event): EventSourcedAggregateRoot
    {
        ++$this->playhead;

        $applier = $this->eventMap[$event::class];

        return $this->{$applier}($event);
    }

    public function getPlayhead(): int
    {
        return $this->playhead;
    }
}
