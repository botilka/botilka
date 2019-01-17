<?php

declare(strict_types=1);

namespace Botilka\EventStore;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Event\Event;

trait AggregateRootEventApplierTrait
{
    /**
     * @param Event[] $events
     */
    private function applyEventsToAggregateRoot(EventSourcedAggregateRoot $instance, array $events): EventSourcedAggregateRoot
    {
        foreach ($events as $event) {
            $instance = $instance->apply($event);
        }

        return $instance;
    }
}
