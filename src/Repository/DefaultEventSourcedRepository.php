<?php

declare(strict_types=1);

namespace Botilka\Repository;

use Botilka\Application\Command\EventSourcedCommandResponse;
use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\EventStore\AggregateRootEventApplierTrait;
use Botilka\EventStore\EventStore;

/**
 * This class is a default event sourced repository that just load & persist from the event store.
 * The EventSourcedAggregateRoot is instanciated without any parameters.
 * Feel free to decorate/override it.
 */
final class DefaultEventSourcedRepository implements EventSourcedRepository
{
    use AggregateRootEventApplierTrait;

    private $eventStore;
    private $aggregateRootClassName;

    public function __construct(EventStore $eventStore, string $aggregateRootClassName)
    {
        $this->eventStore = $eventStore;
        $this->aggregateRootClassName = $aggregateRootClassName;
    }

    public function load(string $id): EventSourcedAggregateRoot
    {
        $events = $this->eventStore->load($id);
        /** @var EventSourcedAggregateRoot $instance */
        $instance = new $this->aggregateRootClassName();

        return $this->applyEventsToAggregateRoot($instance, $events);
    }

    public function save(EventSourcedCommandResponse $commandResponse): void
    {
        $event = $commandResponse->getEvent();
        $this->eventStore->append($commandResponse->getId(), $commandResponse->getPlayhead(), \get_class($event), $event, null, new \DateTimeImmutable(), $commandResponse->getDomain());
    }
}
