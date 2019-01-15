<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine;

use Botilka\EventStore\ManagedEvent;
use Botilka\EventStore\EventStoreManager;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class EventStoreManagerDoctrine implements EventStoreManager
{
    private $connection;
    private $denormalizer;
    private $tableName;

    public function __construct(Connection $connection, DenormalizerInterface $denormalizer, string $tableName)
    {
        $this->connection = $connection;
        $this->denormalizer = $denormalizer;
        $this->tableName = $tableName;
    }

    public function loadByAggregateRootId(string $id, ?int $from = null, ?int $to = null): iterable
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

        $stmt = $this->connection->prepare("$query ORDER BY playhead");
        $stmt->execute($parameters);

        return $this->deserialize($stmt->fetchAll());
    }

    public function loadByDomain(string $domain): iterable
    {
        $query = "SELECT * FROM {$this->tableName} WHERE domain = :domain";

        $stmt = $this->connection->prepare("$query ORDER BY playhead");
        $stmt->execute(['domain' => $domain]);

        return $this->deserialize($stmt->fetchAll());
    }

    public function getAggregateRootIds(): array
    {
        return $this->getDistinct('id');
    }

    public function getDomains(): array
    {
        return $this->getDistinct('domain');
    }

    private function getDistinct(string $column): array
    {
        $query = "SELECT DISTINCT $column FROM {$this->tableName}";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();

        return \array_map(function ($row) use ($column) {
            return $row[$column];
        }, $stmt->fetchAll());
    }

    /**
     * @return ManagedEvent[]
     */
    private function deserialize(array $storedEvents): array
    {
        $events = [];

        /* @var array $event */
        foreach ($storedEvents as $storedEvent) {
            $events[] = new ManagedEvent(
                $storedEvent['id'],
                $this->denormalizer->denormalize(\json_decode($storedEvent['payload'], true), $storedEvent['type']),
                $storedEvent['playhead'],
                \json_decode($storedEvent['metadata'], true),
                new \DateTimeImmutable($storedEvent['recorded_on']),
                $storedEvent['domain']
            );
        }

        return $events;
    }
}
