<?php

namespace Botilka\Domain;

use Botilka\Event\Event;

interface EventSourcedAggregateRoot extends AggregateRoot
{
    public function apply(Event $event): EventSourcedAggregateRoot;
}
