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
    private $table;

    public function __construct(Connection $connection, DenormalizerInterface $denormalizer, string $table)
    {
        $this->connection = $connection;
        $this->denormalizer = $denormalizer;
        $this->table = $table;
    }

    public function load(string $id, ?int $from = null, ?int $to = null): array
    {
        $parameters = ['id' => $id];
        $query = "SELECT * FROM {$this->table} WHERE id = :id";

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

    public function getAggregateRootIds(): array
    {
        $query = "SELECT DISTINCT id FROM {$this->table}";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();

        return \array_map(function ($row) {
            return $row['id'];
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
                $this->denormalizer->denormalize(\json_decode($storedEvent['payload'], true), $storedEvent['type']),
                $storedEvent['playhead'],
                \json_decode($storedEvent['metadata'], true),
                new \DateTimeImmutable($storedEvent['recorded_on'])
            );
        }

        return $events;
    }
}
