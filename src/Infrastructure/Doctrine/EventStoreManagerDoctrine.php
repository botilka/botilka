<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine;

use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class EventStoreManagerDoctrine implements EventStoreManager
{
    public function __construct(
        private Connection $connection,
        private DenormalizerInterface $denormalizer,
        private string $tableName,
    ) {}

    public function loadByAggregateRootId(string $id, int $from = null, int $to = null): iterable
    {
        $parameters = ['id' => $id];

        $query = "SELECT * FROM {$this->tableName} WHERE id = :id";

        if (null !== $from) {
            $query .= ' AND playhead >= :from';
            $parameters['from'] = $from;
        }

        if (null !== $to) {
            $query .= ' AND playhead <= :to';
            $parameters['to'] = $to;
        }

        $stmt = $this->connection->prepare("{$query} ORDER BY playhead");
        $result = $stmt->execute($parameters);

        /** @var array<int, array{id: string, playhead: int, payload: string, metadata: ?string, type: class-string, recorded_on: string, domain: string}> $events */
        $events = $result->fetchAllAssociative();

        return $this->deserialize($events);
    }

    public function loadByDomain(string $domain): iterable
    {
        $query = "SELECT * FROM {$this->tableName} WHERE domain = :domain ORDER BY playhead";

        $stmt = $this->connection->prepare($query);
        $result = $stmt->execute(['domain' => $domain]);

        /** @var array<int, array{id: string, playhead: int, payload: string, metadata: ?string, type: class-string, recorded_on: string, domain: string}> $events */
        $events = $result->fetchAllAssociative();

        return $this->deserialize($events);
    }

    public function getAggregateRootIds(): array
    {
        return $this->getDistinct('id');
    }

    public function getDomains(): array
    {
        return $this->getDistinct('domain');
    }

    /**
     * @return string[]
     */
    private function getDistinct(string $column): array
    {
        $query = "SELECT DISTINCT {$column} FROM {$this->tableName}";
        $stmt = $this->connection->prepare($query);
        $result = $stmt->execute();

        /** @var string[][] $values */
        $values = $result->fetchAllAssociative();

        /** @var string[] */
        return array_map(static fn ($row) => $row[$column], $values);
    }

    /**
     * @param array<int, array{id: string, playhead: int, payload: string, metadata: ?string, type: class-string, recorded_on: string, domain: string}> $storedEvents
     *
     * @return ManagedEvent[]
     */
    private function deserialize(array $storedEvents): array
    {
        $events = [];

        foreach ($storedEvents as $storedEvent) {
            /** @var \Botilka\Event\Event $domainEvent */
            $domainEvent = $this->denormalizer->denormalize(json_decode($storedEvent['payload'], true, 512, \JSON_THROW_ON_ERROR), $storedEvent['type']);
            /** @var array<string, mixed>|null $metadata */
            $metadata = null !== $storedEvent['metadata'] ? json_decode($storedEvent['metadata'], true, 512, \JSON_THROW_ON_ERROR) : null;
            $events[] = new ManagedEvent(
                $storedEvent['id'],
                $domainEvent,
                $storedEvent['playhead'],
                $metadata,
                new \DateTimeImmutable($storedEvent['recorded_on']),
                $storedEvent['domain']
            );
        }

        return $events;
    }
}
