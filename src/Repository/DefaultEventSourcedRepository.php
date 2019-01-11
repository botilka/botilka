<?php

declare(strict_types=1);

namespace Botilka\Repository;

use Botilka\Application\Command\EventSourcedCommandResponse;
use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\EventStore\EventStore;

final class DefaultEventSourcedRepository implements EventSourcedRepository
{
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

        foreach ($events as $event) {
            $instance = $instance->apply($event);
        }

        return $instance;
    }

    public function save(EventSourcedCommandResponse $commandResponse): void
    {
        $event = $commandResponse->getEvent();
        $this->eventStore->append($commandResponse->getId(), $commandResponse->getPlayhead(), \get_class($event), $event, null, new \DateTimeImmutable(), $commandResponse->getDomain());
    }
}
