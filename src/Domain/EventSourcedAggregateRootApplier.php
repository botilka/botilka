<?php

namespace Botilka\Domain;

use Botilka\Event\Event;

trait EventSourcedAggregateRootApplier
{
    private $playhead = -1;

    public function apply(Event $event): EventSourcedAggregateRoot
    {
        ++$this->playhead;

        $applier = $this->eventMap[\get_class($event)];

        return $this->$applier($event);
    }

    public function getPlayhead(): int
    {
        return $this->playhead;
    }
}
