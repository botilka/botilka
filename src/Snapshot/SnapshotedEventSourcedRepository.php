<?php

declare(strict_types=1);

namespace Botilka\Snapshot;

use Botilka\Application\Command\EventSourcedCommandResponse;
use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\EventStore\AggregateRootEventApplierTrait;
use Botilka\EventStore\EventStore;
use Botilka\Repository\EventSourcedRepository;
use Botilka\Snapshot\Strategist\SnapshotStrategist;

final readonly class SnapshotedEventSourcedRepository implements EventSourcedRepository
{
    use AggregateRootEventApplierTrait;

    public function __construct(
        private SnapshotStore $snapshotStore,
        private SnapshotStrategist $strategist,
        private EventSourcedRepository $eventSourcedRepository,
        private EventStore $eventStore,
    ) {}

    public function load(string $id): EventSourcedAggregateRoot
    {
        try {
            $instance = $this->snapshotStore->load($id);
        } catch (SnapshotNotFoundException) {
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
