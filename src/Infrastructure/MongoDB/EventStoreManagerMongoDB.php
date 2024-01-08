<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\MongoDB;

use Botilka\Event\Event;
use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;
use MongoDB\Collection;
use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONDocument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class EventStoreManagerMongoDB implements EventStoreManager
{
    public function __construct(
        private Collection $collection,
        private DenormalizerInterface $denormalizer,
    ) {}

    public function loadByDomain(string $domain): iterable
    {
        return $this->deserialize($this->collection->find(['domain' => $domain], ['sort' => ['playhead' => 1]]));
    }

    public function loadByAggregateRootId(string $id, int $from = null, int $to = null): iterable
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

    public function getDomains(): array
    {
        return $this->collection->distinct('domain');
    }

    /**
     * @param Cursor<BSONDocument> $storedEvents
     *
     * @return ManagedEvent[]
     */
    private function deserialize(Cursor $storedEvents): array
    {
        $events = [];
        /** @var BSONDocument<array<string, mixed>> $storedEvent */
        foreach ($storedEvents as $storedEvent) {
            /** @var Event $domainEvent */
            $domainEvent = $this->denormalizer->denormalize($storedEvent->offsetGet('payload'), $storedEvent->offsetGet('type'));
            $events[] = new ManagedEvent(
                $storedEvent->offsetGet('id'),
                $domainEvent,
                $storedEvent->offsetGet('playhead'),
                $storedEvent->offsetGet('metadata')->getArrayCopy(),
                new \DateTimeImmutable($storedEvent->offsetGet('recordedOn')),
                $storedEvent->offsetGet('domain')
            );
        }

        return $events;
    }
}
