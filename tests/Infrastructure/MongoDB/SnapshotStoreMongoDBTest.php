<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\Infrastructure\MongoDB\SnapshotStoreMongoDB;
use Botilka\Snapshot\SnapshotNotFoundException;
use Botilka\Tests\Fixtures\Domain\StubEventSourcedAggregateRoot;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SnapshotStoreMongoDBTest extends TestCase
{
    /** @var Collection|MockObject */
    private \PHPUnit\Framework\MockObject\MockObject $collection;

    private SnapshotStoreMongoDB $snapshotStore;

    protected function setUp(): void
    {
        $this->collection = $this->createMock(Collection::class);
        $this->snapshotStore = new SnapshotStoreMongoDB($this->collection);
    }

    public function testLoadSuccess(): void
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $this->collection->expects(self::once())
            ->method('countDocuments')
            ->with(['id' => 'foo'])
            ->willReturn(1)
        ;

        $result = new BSONDocument(['data' => serialize($aggregateRoot)]);

        $this->collection->expects(self::once())
            ->method('findOne')
            ->with(['id' => 'foo'])
            ->willReturn($result)
        ;

        self::assertSame($aggregateRoot->getAggregateRootId(), $this->snapshotStore->load('foo')->getAggregateRootId());
    }

    public function testLoadFail(): void
    {
        new StubEventSourcedAggregateRoot();

        $this->collection->expects(self::once())
            ->method('countDocuments')
            ->with(['id' => 'foo'])
            ->willReturn(0)
        ;

        $this->collection->expects(self::never())
            ->method('findOne')
        ;

        $this->expectException(SnapshotNotFoundException::class);
        $this->expectExceptionMessage('No snapshot found for foo.');

        $this->snapshotStore->load('foo');
    }

    public function testSnapshot(): void
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $this->collection->expects(self::once())
            ->method('updateOne')
            ->with(['id' => $aggregateRoot->getAggregateRootId()],
                ['$set' => ['data' => serialize($aggregateRoot)]],
                ['upsert' => true]
            )
        ;

        $this->snapshotStore->snapshot($aggregateRoot);
    }
}
