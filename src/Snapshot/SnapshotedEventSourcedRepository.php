<?php

declare(strict_types=1);

namespace Botilka\Snapshot;

use Botilka\Application\Command\EventSourcedCommandResponse;
use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\EventStore\AggregateRootEventApplierTrait;
use Botilka\EventStore\EventStore;
use Botilka\Repository\EventSourcedRepository;
use Botilka\Snapshot\Strategist\SnapshotStrategist;

final class SnapshotedEventSourcedRepository implements EventSourcedRepository
{
    use AggregateRootEventApplierTrait;

    private $snapshotStore;
    private $strategist;
    private $eventSourcedRepository; // default event sourced repository
    private $eventStore;

    public function __construct(SnapshotStore $snapshotStore, SnapshotStrategist $strategist, EventSourcedRepository $eventSourcedRepository, EventStore $eventStore)
    {
        $this->snapshotStore = $snapshotStore;
        $this->strategist = $strategist;
        $this->eventSourcedRepository = $eventSourcedRepository;
        $this->eventStore = $eventStore;
    }

    public function load(string $id): EventSourcedAggregateRoot
    {
        try {
            $instance = $this->snapshotStore->load($id);
        } catch (SnapshotNotFoundException $e) {
            return $this->eventSourcedRepository->load($id);
        }

        $events = $this->eventStore->loadFromPlayhead($id, $instance->getPlayhead() + 1);

        return $this->applyEventsToAggregateRoot($instance, $events);
    }

    public function save(EventSourcedCommandResponse $commandResponse): void
    {
        $aggregateRoot = $commandResponse->getAggregateRoot();

        if ($this->strategist->mustSnapshot($aggregateRoot)) {
            $this->snapshotStore->snapshot($aggregateRoot);
        }

        $this->eventSourcedRepository->save($commandResponse);
    }
}
