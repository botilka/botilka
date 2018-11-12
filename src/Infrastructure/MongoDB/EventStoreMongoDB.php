<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\MongoDB;

use Botilka\Event\Event;
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
        return $this->deserialize(
            $this->collection->find(['id' => $id], ['sort' => ['payload' => -1]])
        );
    }

    public function loadFromPlayhead(string $id, int $fromPlayhead): array
    {
        return $this->deserialize(
            $this->collection->find([
                'id' => $id,
                'playhead' => ['$gte' => $fromPlayhead],
            ], ['sort' => ['payload' => -1]])
        );
    }

    public function loadFromPlayheadToPlayhead(string $id, int $fromPlayhead, int $toPlayhead): array
    {
        return $this->deserialize(
            $this->collection->find([
                'id' => $id,
                'playhead' => ['$gte' => $fromPlayhead, '$lte' => $fromPlayhead],
            ], ['sort' => ['payload' => -1]])
        );
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

    public function append(string $id, int $playhead, string $type, Event $payload, ?array $metadata, \DateTimeImmutable $recordedOn): void
    {
        $values = [
            'id' => $id,
            'playhead' => $playhead,
            'type' => $type,
            'payload' => $this->normalizer->normalize($payload),
            'metadata' => $this->normalizer->normalize($metadata),
            'recordedOn' => $recordedOn->format('Y-m-d H:i:s.u'),
        ];

        try {
            $this->collection->insertOne($values);
        } catch (BulkWriteException $e) {
            throw new EventStoreConcurrencyException(\sprintf('Duplicate storage of event "%s" on aggregate "%s" with playhead %d.', $values['type'], $values['id'], $values['playhead']));
        }
    }
}
