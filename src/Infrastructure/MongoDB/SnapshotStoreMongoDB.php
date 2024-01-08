<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\MongoDB;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Snapshot\SnapshotNotFoundException;
use Botilka\Snapshot\SnapshotStore;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;

final readonly class SnapshotStoreMongoDB implements SnapshotStore
{
    public function __construct(private Collection $collection) {}

    public function load(string $id): EventSourcedAggregateRoot
    {
        $criteria = ['id' => $id];
        if (0 === $this->collection->countDocuments($criteria)) {
            throw new SnapshotNotFoundException("No snapshot found for {$id}.");
        }

        /** @var BSONDocument<EventSourcedAggregateRoot> $snapshot */
        $snapshot = $this->collection->findOne(['id' => $id]);

        return unserialize($snapshot->offsetGet('data'));
    }

    public function snapshot(EventSourcedAggregateRoot $aggregateRoot): void
    {
        $this->collection->updateOne(
            ['id' => $aggregateRoot->getAggregateRootId()],
            ['$set' => ['data' => serialize($aggregateRoot)]],
            ['upsert' => true]
        );
    }
}
