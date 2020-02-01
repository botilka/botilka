<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine;

use Botilka\Infrastructure\Doctrine\SnapshotStoreDoctrine;
use Botilka\Snapshot\SnapshotNotFoundException;
use Botilka\Snapshot\SnapshotStore;
use Botilka\Tests\Fixtures\Domain\StubEventSourcedAggregateRoot;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Statement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

class SnapshotStoreDoctrineTest extends TestCase
{
    /** @var Connection|MockObject */
    private $connection;
    /** @var SerializerInterface|MockObject */
    private $serializer;
    /** @var SnapshotStoreDoctrine */
    private $snapshotStore;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->snapshotStore = new SnapshotStoreDoctrine($this->connection, 'snapshot_store', $this->serializer);
        self::assertInstanceOf(SnapshotStore::class, $this->snapshotStore);
    }

    public function testSnapshot()
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $stmtDelete = $this->createMock(Statement::class);
        $stmtInsert = $this->createMock(Statement::class);

        $this->connection->expects(self::exactly(2))
            ->method('prepare')
            ->withConsecutive(['DELETE FROM snapshot_store WHERE id = :id'], ['INSERT INTO snapshot_store VALUES (:id, :playhead, :type, :payload)'])
            ->willReturnOnConsecutiveCalls($stmtDelete, $stmtInsert)
        ;

        $stmtDelete->expects(self::once())
            ->method('execute')
            ->with(['id' => $aggregateRoot->getAggregateRootId()])
        ;

        $this->serializer->expects(self::once())
            ->method('serialize')
            ->with($aggregateRoot, 'json')
            ->willReturn('foobarbaz')
        ;

        $stmtInsert->expects(self::once())
            ->method('execute')
            ->with([
                'id' => $aggregateRoot->getAggregateRootId(),
                'type' => \get_class($aggregateRoot),
                'playhead' => $aggregateRoot->getPlayhead(),
                'payload' => 'foobarbaz',
            ])
        ;

        $this->snapshotStore->snapshot($aggregateRoot);
    }

    public function testLoadSuccess()
    {
        $this->connection->expects(self::once())
            ->method('prepare')
            ->with('SELECT type, payload FROM snapshot_store WHERE id = :id')
            ->willReturn($stmt = $this->getStatement(true))
        ;

        $stmt->expects(self::once())
            ->method('execute')
            ->with(['id' => 'foo'])
        ;

        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $this->serializer->expects(self::once())
            ->method('deserialize')
            ->with(\json_encode(['foo' => 'bar']), 'Foo\\Bar', 'json')
            ->willReturn($aggregateRoot)
        ;

        self::assertSame($aggregateRoot, $this->snapshotStore->load('foo'));
    }

    public function testLoadFail()
    {
        $this->connection->expects(self::once())
            ->method('prepare')
            ->with('SELECT type, payload FROM snapshot_store WHERE id = :id')
            ->willReturn($stmt = $this->getStatement(false))
        ;

        $stmt->expects(self::once())
            ->method('execute')
            ->with(['id' => 'foo'])
        ;

        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $this->serializer->expects(self::never())
            ->method('deserialize')
        ;

        $this->expectException(SnapshotNotFoundException::class);
        $this->expectExceptionMessage('No snapshot found for foo.');

        $this->snapshotStore->load('foo');
    }

    private function getStatement(bool $withResult): MockObject
    {
        $stmt = $this->createMock(Statement::class);

        $result = $withResult ?
            ['type' => 'Foo\\Bar', 'payload' => \json_encode(['foo' => 'bar'])]
        : false;

        $stmt->expects(self::once())
            ->method('fetch')
            ->willReturn($result)
        ;

        return $stmt;
    }
}
