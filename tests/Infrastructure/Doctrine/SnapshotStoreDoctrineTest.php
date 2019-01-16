<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine;

use Botilka\Infrastructure\Doctrine\SnapshotStoreDoctrine;
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

    protected function setUp()
    {
        $this->connection = $this->createMock(Connection::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->snapshotStore = new SnapshotStoreDoctrine($this->connection, 'snapshot_store', $this->serializer);
        $this->assertInstanceOf(SnapshotStore::class, $this->snapshotStore);
    }

    private function getStatement(bool $withResult): MockObject
    {
        $stmt = $this->createMock(Statement::class);

        $result = $withResult ?
            ['type' => 'Foo\\Bar', 'payload' => \json_encode(['foo' => 'bar'])]
        : false;

        $stmt->expects($this->once())
            ->method('fetch')
            ->willReturn($result);

        return $stmt;
    }

    public function testSnapshot()
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $stmtDelete = $this->createMock(Statement::class);
        $stmtInsert = $this->createMock(Statement::class);

        $this->connection->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive(['DELETE FROM snapshot_store WHERE id = :id'], ['INSERT INTO snapshot_store VALUES (:id, :playhead, :type, :payload)'])
            ->willReturnOnConsecutiveCalls($stmtDelete, $stmtInsert);

        $stmtDelete->expects($this->once())
            ->method('execute')
            ->with(['id' => $aggregateRoot->getAggregateRootId()]);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($aggregateRoot, 'json')
            ->willReturn('foobarbaz');

        $stmtInsert->expects($this->once())
            ->method('execute')
            ->with([
                'id' => $aggregateRoot->getAggregateRootId(),
                'type' => \get_class($aggregateRoot),
                'playhead' => $aggregateRoot->getPlayhead(),
                'payload' => 'foobarbaz',
            ]);

        $this->snapshotStore->snapshot($aggregateRoot);
    }

    public function testLoadSuccess()
    {
        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('SELECT type, payload FROM snapshot_store WHERE id = :id')
            ->willReturn($stmt = $this->getStatement(true));

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['id' => 'foo']);

        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(\json_encode(['foo' => 'bar']), 'Foo\\Bar', 'json')
            ->willReturn($aggregateRoot);

        $this->assertSame($aggregateRoot, $this->snapshotStore->load('foo'));
    }

    /**
     * @expectedException \Botilka\Snapshot\SnapshotNotFoundException
     * @expectedExceptionMessage No snapshot found for foo.
     */
    public function testLoadFail()
    {
        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('SELECT type, payload FROM snapshot_store WHERE id = :id')
            ->willReturn($stmt = $this->getStatement(false));

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['id' => 'foo']);

        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $this->serializer->expects($this->never())
            ->method('deserialize');

        $this->snapshotStore->load('foo');
    }
}
