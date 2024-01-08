<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Snapshot\SnapshotNotFoundException;
use Botilka\Snapshot\SnapshotStore;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class SnapshotStoreDoctrine implements SnapshotStore
{
    public function __construct(
        private Connection $connection,
        private string $tableName,
        private SerializerInterface $serializer,
    ) {}

    public function load(string $id): EventSourcedAggregateRoot
    {
        $stmt = $this->connection->prepare("SELECT type, payload FROM {$this->tableName} WHERE id = :id");
        /** @var array{payload: string, type: string}|false $result */
        $result = $stmt->executeQuery(['id' => $id])->fetchAssociative();

        if (false === $result) {
            throw new SnapshotNotFoundException("No snapshot found for {$id}.");
        }

        /** @var EventSourcedAggregateRoot */
        return $this->serializer->deserialize($result['payload'], $result['type'], 'json');
    }

    public function snapshot(EventSourcedAggregateRoot $aggregateRoot): void
    {
        $id = $aggregateRoot->getAggregateRootId();

        $stmt = $this->connection->prepare("DELETE FROM {$this->tableName} WHERE id = :id");
        $stmt->bindValue('id', $id);
        $stmt->executeStatement();

        $stmt = $this->connection->prepare("INSERT INTO {$this->tableName} VALUES (:id, :playhead, :type, :payload)");
        $stmt->bindValue('id', $id);
        $stmt->bindValue('type', $aggregateRoot::class);
        $stmt->bindValue('playhead', $aggregateRoot->getPlayhead());
        $stmt->bindValue('payload', $this->serializer->serialize($aggregateRoot, 'json'));
        $stmt->executeStatement();
    }
}
