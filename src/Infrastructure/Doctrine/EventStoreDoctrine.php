<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine;

use Botilka\Event\Event as DomainEvent;
use Botilka\EventStore\AggregateRootNotFoundException;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class EventStoreDoctrine implements EventStore
{
    public function __construct(private Connection $connection, private NormalizerInterface $normalizer, private DenormalizerInterface $denormalizer, private string $tableName) {}

    public function load(string $id): array
    {
        $stmt = $this->connection->prepare("SELECT type, payload FROM {$this->tableName} WHERE id = :id ORDER BY playhead");
        $result = $stmt->execute(['id' => $id]);

        /** @var array<int, array<string, string>> $events */
        $events = $result->fetchAllAssociative();
        if (0 === \count($events)) {
            throw new AggregateRootNotFoundException("No aggregrate root found for {$id}.");
        }

        return $this->deserialize($events);
    }

    public function loadFromPlayhead(string $id, int $fromPlayhead): array
    {
        $stmt = $this->connection->prepare("SELECT type, payload FROM {$this->tableName} WHERE id = :id AND playhead >= :from ORDER BY playhead");
        $result = $stmt->execute(['id' => $id, 'from' => $fromPlayhead]);

        /** @var array<int, array<string, string>> $events */
        $events = $result->fetchAllAssociative();

        return $this->deserialize($events);
    }

    public function loadFromPlayheadToPlayhead(string $id, int $fromPlayhead, int $toPlayhead): array
    {
        $stmt = $this->connection->prepare("SELECT type, payload FROM {$this->tableName} WHERE id = :id AND playhead BETWEEN :from AND :to ORDER BY playhead");
        $result = $stmt->execute(['id' => $id, 'from' => $fromPlayhead, 'to' => $toPlayhead]);

        /** @var array<int, array<string, string>> $events */
        $events = $result->fetchAllAssociative();

        return $this->deserialize($events);
    }

    /**
     * @param array<string, mixed>|null $metadata
     */
    public function append(string $id, int $playhead, string $type, DomainEvent $payload, ?array $metadata, \DateTimeImmutable $recordedOn, string $domain): void
    {
        $stmt = $this->connection->prepare("INSERT INTO {$this->tableName} VALUES (:id, :playhead, :type, :payload, :metadata, :recordedOn, :domain)");
        $values = [
            'id' => $id,
            'playhead' => $playhead,
            'type' => $type,
            'payload' => json_encode($this->normalizer->normalize($payload), \JSON_THROW_ON_ERROR),
            'metadata' => json_encode($metadata, \JSON_THROW_ON_ERROR),
            'recordedOn' => $recordedOn->format('Y-m-d H:i:s.u'),
            'domain' => $domain,
        ];

        try {
            $stmt->execute($values);
        } catch (UniqueConstraintViolationException) {
            throw new EventStoreConcurrencyException(sprintf('Duplicate storage of event "%s" on aggregate "%s" with playhead %d.', $values['type'], $values['id'], $values['playhead']));
        }
    }

    /**
     * @param array<int, array<string, string>> $storedEvents
     *
     * @return DomainEvent[]
     */
    private function deserialize(array $storedEvents): array
    {
        $events = [];
        foreach ($storedEvents as $storedEvent) {
            /** @var DomainEvent $event */
            $event = $this->denormalizer->denormalize(json_decode($storedEvent['payload'], true, 512, \JSON_THROW_ON_ERROR), $storedEvent['type']);
            $events[] = $event;
        }

        return $events;
    }
}
