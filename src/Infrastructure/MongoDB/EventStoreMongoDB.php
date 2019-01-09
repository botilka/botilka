<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\MongoDB;

use Botilka\Event\Event;
use Botilka\EventStore\AggregateRootNotFoundException;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use MongoDB\Collection;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Exception\BulkWriteException;
use MongoDB\Model\BSONDocument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class EventStoreMongoDB implements EventStore
{
    private $collection;
    private $normalizer;
    private $denormalizer;

    public function __construct(Collection $collection, NormalizerInterface $normalizer, DenormalizerInterface $denormalizer)
    {
        $this->collection = $collection;
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
    }

    public function load(string $id): array
    {
        $criteria = ['id' => $id];
        if (0 === $this->collection->count($criteria)) {
            throw new AggregateRootNotFoundException("No aggregrate root found for $id.");
        }

        $events = $this->collection->find(['id' => $id], ['sort' => ['playhead' => 1]]);

        return $this->deserialize($events);
    }

    public function loadFromPlayhead(string $id, int $fromPlayhead): array
    {
        $criteria = [
            'id' => $id,
            'playhead' => ['$gte' => $fromPlayhead],
        ];

        if (0 === $this->collection->count($criteria)) {
            throw new AggregateRootNotFoundException("No aggregrate root found for $id from playhead $fromPlayhead.");
        }

        $events = $this->collection->find($criteria, ['sort' => ['playhead' => 1]]);

        return $this->deserialize($events);
    }

    public function loadFromPlayheadToPlayhead(string $id, int $fromPlayhead, int $toPlayhead): array
    {
        $criteria = [
            'id' => $id,
            'playhead' => ['$gte' => $fromPlayhead, '$lte' => $toPlayhead],
        ];

        if (0 === $this->collection->count($criteria)) {
            throw new AggregateRootNotFoundException("No aggregrate root found for $id from playhead $fromPlayhead to playhead $toPlayhead.");
        }

        $events = $this->collection->find($criteria, ['sort' => ['playhead' => 1]]);

        return $this->deserialize($events);
    }

    public function append(string $id, int $playhead, string $type, Event $payload, ?array $metadata, \DateTimeImmutable $recordedOn, string $domain): void
    {
        $values = [
            'id' => $id,
            'playhead' => $playhead,
            'type' => $type,
            'payload' => $this->normalizer->normalize($payload),
            'metadata' => $this->normalizer->normalize($metadata),
            'recordedOn' => $recordedOn->format('Y-m-d H:i:s.u'),
            'domain' => $domain,
        ];

        try {
            $this->collection->insertOne($values);
        } catch (BulkWriteException $e) {
            throw new EventStoreConcurrencyException(\sprintf('Duplicate storage of event "%s" on aggregate "%s" with playhead %d.', $values['type'], $values['id'], $values['playhead']));
        }
    }

    /**
     * @return Event[]
     */
    private function deserialize(Cursor $cursor): array
    {
        $events = [];
        /** @var BSONDocument $event */
        foreach ($cursor as $event) {
            $events[] = $this->denormalizer->denormalize($event->offsetGet('payload'), $event->offsetGet('type'));
        }

        return $events;
    }
}
