<?php

declare(strict_types=1);

namespace Botilka\Domain;

use Botilka\Event\Event;

trait EventSourcedAggregateRootApplier
{
    /** @var int */
    private $playhead = -1;

    public function apply(Event $event): EventSourcedAggregateRoot
    {
        ++$this->playhead;

        $applier = $this->eventMap[\get_class($event)];

        return $this->{$applier}($event);
    }

    public function getPlayhead(): int
    {
        return $this->playhead;
    }
}
