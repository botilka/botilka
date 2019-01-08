<?php

declare(strict_types=1);

namespace Botilka\Repository;

use Botilka\Application\Command\EventSourcedCommandResponse;
use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\EventStore\EventStore;

final class DefaultEventSourcedRepository implements EventSourcedRepository
{
    private $eventStore;
    private $aggregateRootClass;

    public function __construct(EventStore $eventStore, string $aggregateRootClass)
    {
        $this->eventStore = $eventStore;
        $this->aggregateRootClass = $aggregateRootClass;
    }

    public function load(string $id): EventSourcedAggregateRoot
    {
        $events = $this->eventStore->load($id);
        /** @var EventSourcedAggregateRoot $instance */
        $instance = new $this->aggregateRootClass();

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
