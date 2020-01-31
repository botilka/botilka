<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Snapshot\SnapshotNotFoundException;
use Botilka\Snapshot\SnapshotStore;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Serializer\SerializerInterface;

final class SnapshotStoreDoctrine implements SnapshotStore
{
    private const FORMAT = 'json';
    private $connection;
    private $tableName;
    private $serializer;

    public function __construct(Connection $connection, string $tableName, SerializerInterface $serializer)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->serializer = $serializer;
    }

    public function load(string $id): EventSourcedAggregateRoot
    {
        $stmt = $this->connection->prepare("SELECT type, payload FROM {$this->tableName} WHERE id = :id");
        $stmt->execute(['id' => $id]);

        if (false === ($result = $stmt->fetch())) {
            throw new SnapshotNotFoundException("No snapshot found for {$id}.");
        }
        /** @var EventSourcedAggregateRoot $result */
        $result = $this->serializer->deserialize($result['payload'], $result['type'], self::FORMAT);

        return $result;
    }

    public function snapshot(EventSourcedAggregateRoot $aggregateRoot): void
    {
        $id = $aggregateRoot->getAggregateRootId();

        $stmt = $this->connection->prepare("DELETE FROM {$this->tableName} WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $stmt = $this->connection->prepare("INSERT INTO {$this->tableName} VALUES (:id, :playhead, :type, :payload)");
        $stmt->execute([
            'id' => $id,
            'type' => \get_class($aggregateRoot),
            'playhead' => $aggregateRoot->getPlayhead(),
            'payload' => $this->serializer->serialize($aggregateRoot, self::FORMAT),
        ]);
    }
}
