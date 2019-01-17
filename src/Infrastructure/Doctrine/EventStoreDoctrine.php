<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine;

use Botilka\Event\Event as DomainEvent;
use Botilka\EventStore\AggregateRootNotFoundException;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class EventStoreDoctrine implements EventStore
{
    private $connection;
    private $normalizer;
    private $denormalizer;
    private $tableName;

    public function __construct(Connection $connection, NormalizerInterface $normalizer, DenormalizerInterface $denormalizer, string $tableName)
    {
        $this->connection = $connection;
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->tableName = $tableName;
    }

    public function load(string $id): array
    {
        $stmt = $this->connection->prepare("SELECT type, payload FROM {$this->tableName} WHERE id = :id ORDER BY playhead");
        $stmt->execute(['id' => $id]);

        if (0 === \count($events = $stmt->fetchAll())) {
            throw new AggregateRootNotFoundException("No aggregrate root found for $id.");
        }

        return $this->deserialize($events);
    }

    public function loadFromPlayhead(string $id, int $fromPlayhead): array
    {
        $stmt = $this->connection->prepare("SELECT type, payload FROM {$this->tableName} WHERE id = :id AND playhead >= :from ORDER BY playhead");
        $stmt->execute(['id' => $id, 'from' => $fromPlayhead]);

        return $this->deserialize($stmt->fetchAll());
    }

    public function loadFromPlayheadToPlayhead(string $id, int $fromPlayhead, int $toPlayhead): array
    {
        $stmt = $this->connection->prepare("SELECT type, payload FROM {$this->tableName} WHERE id = :id AND playhead BETWEEN :from AND :to ORDER BY playhead");
        $stmt->execute(['id' => $id, 'from' => $fromPlayhead, 'to' => $toPlayhead]);

        return $this->deserialize($stmt->fetchAll());
    }

    public function append(string $id, int $playhead, string $type, DomainEvent $payload, ?array $metadata, \DateTimeImmutable $recordedOn, string $domain): void
    {
        $stmt = $this->connection->prepare("INSERT INTO {$this->tableName} VALUES (:id, :playhead, :type, :payload, :metadata, :recordedOn, :domain)");

        $values = [
            'id' => $id,
            'playhead' => $playhead,
            'type' => $type,
            'payload' => \json_encode($this->normalizer->normalize($payload)),
            'metadata' => \json_encode($metadata),
            'recordedOn' => $recordedOn->format('Y-m-d H:i:s.u'),
            'domain' => $domain,
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
    private function deserialize(array $storedEvents): array
    {
        $events = [];
        /** @var array $event */
        foreach ($storedEvents as $event) {
            $events[] = $this->denormalizer->denormalize(\json_decode($event['payload'], true), $event['type']);
        }

        return $events;
    }
}
