<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\InMemory;

use Botilka\Infrastructure\InMemory\SnapshotStoreInMemory;
use Botilka\Tests\Fixtures\Domain\StubEventSourcedAggregateRoot;
use PHPUnit\Framework\TestCase;

class SnapshotStoreInMemoryTest extends TestCase
{
    /** @var SnapshotStoreInMemory */
    private $snapshotStore;

    protected function setUp()
    {
        $this->snapshotStore = new SnapshotStoreInMemory();
    }

    /** @expectedException \Botilka\Snapshot\SnapshotNotFoundException */
    public function testLoadNotFound()
    {
        $this->snapshotStore->load('non_existent');
    }

    public function testSnapshotAndLoad()
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();
        $this->snapshotStore->snapshot($aggregateRoot);

        /** @var StubEventSourcedAggregateRoot $aggregateRootFromSnapshotStore */
        $aggregateRootFromSnapshotStore = $this->snapshotStore->load($aggregateRoot->getAggregateRootId());
        self::assertSame($aggregateRoot, $aggregateRootFromSnapshotStore);
        self::assertSame(-1, $aggregateRootFromSnapshotStore->getPlayhead());

        [$aggregateRoot, $event] = $aggregateRoot = $aggregateRoot->stub(456);
        $this->snapshotStore->snapshot($aggregateRoot);

        /** @var StubEventSourcedAggregateRoot $aggregateRootFromSnapshotStore */
        $aggregateRootFromSnapshotStore = $this->snapshotStore->load($aggregateRoot->getAggregateRootId());
        self::assertSame($aggregateRoot, $aggregateRootFromSnapshotStore);
        self::assertSame(456, $aggregateRootFromSnapshotStore->getFoo());
        self::assertSame(0, $aggregateRootFromSnapshotStore->getPlayhead());
    }
}
