<?php

declare(strict_types=1);

namespace Botilka\Snapshot;

use Botilka\Application\Command\EventSourcedCommandResponse;
use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\EventStore\EventStore;
use Botilka\Repository\EventSourcedRepository;
use Botilka\Snapshot\Strategist\SnapshotStrategist;

final class SnapshotedEventSourcedRepository implements EventSourcedRepository
{
    private $snapshotStore;
    private $eventStore;
    private $strategist;

    public function __construct(SnapshotStore $snapshotStore, EventStore $eventStore, SnapshotStrategist $strategist)
    {
        $this->snapshotStore = $snapshotStore;
        $this->eventStore = $eventStore;
        $this->strategist = $strategist;
    }

    public function load(string $id): EventSourcedAggregateRoot
    {
        $instance = $this->snapshotStore->load($id);

        $events = null === $instance ? $this->eventStore->load($id) : $this->eventStore->loadFromPlayhead($instance->getPlayhead());

        foreach ($events as $event) {
            $instance = $instance->apply($event);
        }

        return $instance;
    }

    public function save(EventSourcedCommandResponse $commandResponse): void
    {
        $aggregateRoot = $commandResponse->getAggregateRoot();

        if ($this->strategist->mustSnapshot($aggregateRoot)) {
            $this->snapshotStore->snapshot($aggregateRoot);
        }

        $event = $commandResponse->getEvent();
        $this->eventStore->append($commandResponse->getId(), $commandResponse->getPlayhead(), \get_class($event), $event, null, new \DateTimeImmutable(), $commandResponse->getDomain());
    }
}
