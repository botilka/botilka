<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine;

use Botilka\Event\Event as DomainEvent;
use Botilka\EventStore\DefaultManagedEvent;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class EventStoreManagerDoctrine implements EventStoreManager
{
    private $connection;
    private $normalizer;
    private $denormalizer;
    private $table;

    public function __construct(Connection $connection, NormalizerInterface $normalizer, DenormalizerInterface $denormalizer, string $table)
    {
        $this->connection = $connection;
        $this->normalizer = $normalizer;
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
            $query .= ' AND playhead <= :from';
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
     * @return Event[]
     */
    private function deserialize(array $storedEvents): array
    {
        $events = [];
        /** @var array $event */
        foreach ($storedEvents as $event) {
            $events[] = new DefaultManagedEvent(
                $this->denormalizer->denormalize(\json_decode($event['payload'], true), $event['type']),
                $event['playhead'],
                json_decode($event['metadata'], true),
                new \DateTimeImmutable($event['recorded_on'])
            );
        }

        return $events;
    }
}
