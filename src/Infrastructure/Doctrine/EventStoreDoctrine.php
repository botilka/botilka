<?php

namespace Botilka\Infrastructure\Doctrine;

use Botilka\Event\Event as DomainEvent;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class EventStoreDoctrine implements EventStore
{
    private $connection;
    private $serializer;
    private $normalizer;

    public function __construct(Connection $connection, SerializerInterface $serializer, NormalizerInterface $normalizer)
    {
        $this->connection = $connection;
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
    }

    public function load(string $id): array
    {
        $stmt = $this->connection->prepare('SELECT type, payload FROM event_store WHERE id = :id ORDER BY playhead');
        $stmt->execute(['id' => $id]);

        return $this->deserialize($stmt->fetchAll());
    }

    public function loadFromPlayhead(string $id, int $fromPlayhead): array
    {
        $stmt = $this->connection->prepare('SELECT type, payload FROM event_store WHERE id = :id AND playhead > :playhead ORDER BY playhead');
        $stmt->execute(['id' => $id, 'playhead' => $fromPlayhead]);

        return $this->deserialize($stmt->fetchAll());
    }

    public function loadFromPlayheadToPlayhead(string $id, int $fromPlayhead, int $toPlayhead): array
    {
        $stmt = $this->connection->prepare('SELECT type, payload FROM event_store WHERE id = :id AND playhead BETWEEN :from AND :to ORDER BY playhead');
        $stmt->execute(['id' => $id, 'from' => $fromPlayhead, 'to' => $toPlayhead]);

        return $this->deserialize($stmt->fetchAll());
    }

    public function append(string $id, int $playhead, string $type, DomainEvent $payload, ?array $metadata, \DateTimeImmutable $recordedOn): void
    {
        $stmt = $this->connection->prepare('INSERT INTO event_store VALUES (:id, :playhead, :type, :payload, :metadata, :recordedOn)');

        $values = [
            'id' => $id,
            'playhead' => $playhead,
            'type' => $type,
            'payload' => \json_encode($this->normalizer->normalize($payload)),
            'metadata' => \json_encode($metadata),
            'recordedOn' => $recordedOn->format('Y-m-d H:i:s.u'),
        ];

        try {
            $stmt->execute($values);
        } catch (UniqueConstraintViolationException $e) {
            throw new EventStoreConcurrencyException(\sprintf('Duplicate storage of event "%s" on aggregate "%s" with playhead %d.', $values['type'], $values['id'], $values['playhead']));
        }
    }
    /**
     * @return Event[]
     */
    private function deserialize(array $events): array
    {
        $events = [];
        /** @var array $event */
        foreach ($cursor as $event) {
            $events[] = $this->serializer->deserialize($event['payload'], $event['type'], 'json');
        }

        return $events;
    }
}
