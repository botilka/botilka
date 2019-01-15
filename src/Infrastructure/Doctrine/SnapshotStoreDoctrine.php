<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Snapshot\SnapshotNotFoundException;
use Botilka\Snapshot\SnapshotStore;
use Doctrine\DBAL\Driver\Connection;

final class SnapshotStoreDoctrine implements SnapshotStore
{
    private $connection;
    private $table;

    public function __construct(Connection $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function load(string $id): EventSourcedAggregateRoot
    {
        $stmt = $this->connection->prepare("SELECT payload FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);

        if (false === ($result = $stmt->fetch())) {
            throw new SnapshotNotFoundException("No snapshot found for $id.");
        }

        $this->connection->

        return \unserialize(\str_replace('__NULL_BYTE__', "\0", $result['payload']));
    }

    public function snapshot(EventSourcedAggregateRoot $aggregateRoot): void
    {
        $id = $aggregateRoot->getAggregateRootId();

        $stmt = $this->connection->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $stmt = $this->connection->prepare("INSERT INTO {$this->table} VALUES (:id, :playhead, :payload)");
        $stmt->execute([
            'id' => $id,
            'playhead' => $aggregateRoot->getPlayhead(),
            'payload' => \str_replace("\0", '__NULL_BYTE__', \serialize($aggregateRoot)),
        ]);
    }
}
