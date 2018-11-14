<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\MongoDB;

use Botilka\EventStore\ManagedEvent;
use Botilka\EventStore\EventStoreManager;
use MongoDB\Collection;
use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONDocument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class EventStoreManagerMongoDB implements EventStoreManager
{
    private $collection;
    private $denormalizer;

    public function __construct(Collection $collection, DenormalizerInterface $denormalizer)
    {
        $this->collection = $collection;
        $this->denormalizer = $denormalizer;
    }

    public function load(string $id, ?int $from = null, ?int $to = null): array
    {
        $filter = [
            'id' => $id,
            'playhead' => [],
        ];

        if (null !== $from) {
            $filter['playhead']['$gte'] = $from;
        }

        if (null !== $to) {
            $filter['playhead']['$lte'] = $to;
        }

        if (0 === \count($filter['playhead'])) {
            unset($filter['playhead']); // otherwise, response is empty
        }

        return $this->deserialize($this->collection->find($filter, ['sort' => ['playhead' => 1]]));
    }

    public function getAggregateRootIds(): array
    {
        return $this->collection->distinct('id');
    }

    /**
     * @return ManagedEvent[]
     */
    private function deserialize(Cursor $storedEvents): array
    {
        $events = [];
        /* @var BSONDocument $event */
        foreach ($storedEvents as $storedEvent) {
            $events[] = new ManagedEvent(
                $this->denormalizer->denormalize($storedEvent->offsetGet('payload'), $storedEvent->offsetGet('type')),
                $storedEvent->offsetGet('playhead'),
                $storedEvent->offsetGet('metadata'),
                new \DateTimeImmutable($storedEvent->offsetGet('recordedOn'))
            );
        }

        return $events;
    }
}
