<?php

declare(strict_types=1);

namespace Botilka\Tests\Snapshot;

use Botilka\Application\Command\EventSourcedCommandResponse;
use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\EventStore\EventStore;
use Botilka\Repository\EventSourcedRepository;
use Botilka\Snapshot\SnapshotedEventSourcedRepository;
use Botilka\Snapshot\SnapshotNotFoundException;
use Botilka\Snapshot\SnapshotStore;
use Botilka\Snapshot\Strategist\SnapshotStrategist;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Tests\Fixtures\Domain\StubEventSourcedAggregateRoot;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SnapshotedEventSourcedRepositoryTest extends TestCase
{
    /** @var SnapshotedEventSourcedRepository */
    private $repository;
    /** @var SnapshotStore|MockObject */
    private $snapshotStore;
    /** @var SnapshotStrategist|MockObject */
    private $strategist;
    /** @var EventSourcedRepository|MockObject */
    private $eventSourcedRepository;
    /** @var EventStore|MockObject */
    private $eventStore;

    protected function setUp()
    {
        $this->snapshotStore = $this->createMock(SnapshotStore::class);
        $this->strategist = $this->createMock(SnapshotStrategist::class);
        $this->eventSourcedRepository = $this->createMock(EventSourcedRepository::class);
        $this->eventStore = $this->createMock(EventStore::class);
        $this->repository = new SnapshotedEventSourcedRepository($this->snapshotStore, $this->strategist, $this->eventSourcedRepository, $this->eventStore);
    }

    public function testLoadNotFound(): void
    {
        $this->snapshotStore->expects($this->once())
            ->method('load')
            ->with('foo')
            ->willThrowException(new SnapshotNotFoundException());

        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $this->eventSourcedRepository->expects($this->once())
            ->method('load')
            ->with('foo')
            ->willReturn($aggregateRoot);

        $this->assertSame($aggregateRoot, $this->repository->load('foo'));
    }

    public function testLoad(): void
    {
        $aggregateRoot = $this->createMock(EventSourcedAggregateRoot::class);
        $aggregateRoot->expects($this->once())
            ->method('getPlayhead')
            ->willReturn(51);

        $this->snapshotStore->expects($this->once())
            ->method('load')
            ->with('foo')
            ->willReturn($aggregateRoot);

        $events = [new StubEvent(1), new StubEvent(2)];
        $this->eventStore->expects($this->once())
            ->method('loadFromPlayhead')
            ->with('foo', 52)
            ->willReturn($events);

        $instances = [$aggregateRoot];
        for ($i = 0; $i < \count($events); ++$i) {
            $instances[$i + 1] = $this->createMock(EventSourcedAggregateRoot::class);
            $instances[$i]->expects($this->once())
                ->method('apply')
                ->with($events[$i])
                ->willReturn($instances[$i + 1]);
        }

        $this->assertSame(\end($instances), $this->repository->load('foo'));
    }

    /** @dataProvider saveProvider */
    public function testSave(bool $mustSnapshot): void
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();
        [$aggregateRoot, $event] = $aggregateRoot->stub(1);

        $commandResponse = new EventSourcedCommandResponse('foo', $event, $aggregateRoot->getPlayhead(), 'foomain', $aggregateRoot);

        $this->strategist->expects($this->once())
            ->method('mustSnapshot')
            ->with($aggregateRoot)
            ->willReturn($mustSnapshot);

        $this->snapshotStore->expects($mustSnapshot ? $this->once() : $this->never())
            ->method('snapshot')
            ->with($aggregateRoot);

        $this->eventSourcedRepository->expects($this->once())
            ->method('save')
            ->with($commandResponse);

        $this->repository->save($commandResponse);
    }

    public function saveProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
