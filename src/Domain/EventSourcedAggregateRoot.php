<?php

declare(strict_types=1);

namespace Botilka\Domain;

use Botilka\Event\Event;

interface EventSourcedAggregateRoot extends AggregateRoot
{
    public function apply(Event $event): EventSourcedAggregateRoot;

    public function getPlayhead(): int;
}
