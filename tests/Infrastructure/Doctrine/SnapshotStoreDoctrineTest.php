<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine;

use Botilka\Infrastructure\Doctrine\SnapshotStoreDoctrine;
use Botilka\Snapshot\SnapshotNotFoundException;
use Botilka\Snapshot\SnapshotStore;
use Botilka\Tests\Fixtures\Domain\StubEventSourcedAggregateRoot;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @internal
 */
#[CoversClass(SnapshotStoreDoctrine::class)]
final class SnapshotStoreDoctrineTest extends TestCase
{
    private Connection&MockObject $connection;
    private MockObject&SerializerInterface $serializer;
    private SnapshotStoreDoctrine $snapshotStore;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->snapshotStore = new SnapshotStoreDoctrine($this->connection, 'snapshot_store', $this->serializer);
        self::assertInstanceOf(SnapshotStore::class, $this->snapshotStore);
    }

    public function testSnapshot(): void
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $stmtDelete = $this->createMock(Statement::class);
        $stmtInsert = $this->createMock(Statement::class);

        $matcher = self::exactly(2);
        $this->connection
            ->expects($matcher)
            ->method('prepare')
            ->willReturnCallback(function (string $query) use ($matcher, $stmtDelete, $stmtInsert) {
                $invocationCount = $matcher->numberOfInvocations();
                match ($invocationCount) {
                    1 => $this->assertSame('DELETE FROM snapshot_store WHERE id = :id', $query),
                    2 => $this->assertSame('INSERT INTO snapshot_store VALUES (:id, :playhead, :type, :payload)', $query),
                };

                return match ($invocationCount) {
                    1 => $stmtDelete,
                    2 => $stmtInsert,
                };
            })
        ;

        $stmtDelete->expects(self::once())
            ->method('bindValue')
            ->with('id', $aggregateRoot->getAggregateRootId())
        ;
        $stmtDelete->expects(self::once())
            ->method('executeStatement')
        ;

        $this->serializer->expects(self::once())
            ->method('serialize')
            ->with($aggregateRoot, 'json')
            ->willReturn('foobarbaz')
        ;

        $stmtInsert->expects(self::once())
            ->method('executeStatement')
            ->with()
        ;

        $matcher = self::exactly(4);
        $stmtInsert->expects($matcher)
            ->method('bindValue')
            ->willReturnCallback(function (string $param, int|string $value) use ($matcher, $aggregateRoot): void {
                switch ($matcher->numberOfInvocations()) {
                    case 1:
                        $this->assertSame('id', $param);
                        $this->assertSame($aggregateRoot->getAggregateRootId(), $value);
                        break;
                    case 2:
                        $this->assertSame('type', $param);
                        $this->assertSame($aggregateRoot::class, $value);
                        break;
                    case 3:
                        $this->assertSame('playhead', $param);
                        $this->assertSame($aggregateRoot->getPlayhead(), $value);
                        break;
                    case 4:
                        $this->assertSame('payload', $param);
                        $this->assertSame('foobarbaz', $value);
                        break;
                }
            })
        ;

        $this->snapshotStore->snapshot($aggregateRoot);
    }

    public function testLoadSuccess(): void
    {
        $stmt = $this->getStatement(true);
        $this->connection->expects(self::once())
            ->method('prepare')
            ->with('SELECT type, payload FROM snapshot_store WHERE id = :id')
            ->willReturn($stmt)
        ;

        $stmt->expects(self::once())
            ->method('executeQuery')
            ->with(['id' => 'foo'])
        ;

        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $this->serializer->expects(self::once())
            ->method('deserialize')
            ->with(json_encode(['foo' => 'bar']), 'Foo\\Bar', 'json')
            ->willReturn($aggregateRoot)
        ;

        self::assertSame($aggregateRoot, $this->snapshotStore->load('foo'));
    }

    public function testLoadFail(): void
    {
        $stmt = $this->getStatement(false);
        $this->connection->expects(self::once())
            ->method('prepare')
            ->with('SELECT type, payload FROM snapshot_store WHERE id = :id')
            ->willReturn($stmt)
        ;

        $stmt->expects(self::once())
            ->method('executeQuery')
            ->with(['id' => 'foo'])
        ;

        new StubEventSourcedAggregateRoot();

        $this->serializer->expects(self::never())
            ->method('deserialize')
        ;

        $this->expectException(SnapshotNotFoundException::class);
        $this->expectExceptionMessage('No snapshot found for foo.');

        $this->snapshotStore->load('foo');
    }

    private function getStatement(bool $withResult): MockObject&Statement
    {
        $stmt = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);

        $stmt->expects(self::once())
            ->method('executeQuery')
            ->willReturn($result)
        ;

        $result->expects(self::once())->method('fetchAssociative')
            ->willReturn($withResult ?
                ['type' => 'Foo\\Bar', 'payload' => json_encode(['foo' => 'bar'])]
                : false
            )
        ;

        return $stmt;
    }
}
