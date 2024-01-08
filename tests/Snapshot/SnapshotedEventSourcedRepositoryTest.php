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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SnapshotedEventSourcedRepository::class)]
final class SnapshotedEventSourcedRepositoryTest extends TestCase
{
    private SnapshotedEventSourcedRepository $repository;
    private MockObject&SnapshotStore $snapshotStore;
    private MockObject&SnapshotStrategist $strategist;
    private EventSourcedRepository&MockObject $eventSourcedRepository;
    private EventStore&MockObject $eventStore;

    protected function setUp(): void
    {
        $this->snapshotStore = $this->createMock(SnapshotStore::class);
        $this->strategist = $this->createMock(SnapshotStrategist::class);
        $this->eventSourcedRepository = $this->createMock(EventSourcedRepository::class);
        $this->eventStore = $this->createMock(EventStore::class);
        $this->repository = new SnapshotedEventSourcedRepository($this->snapshotStore, $this->strategist, $this->eventSourcedRepository, $this->eventStore);
    }

    public function testLoadNotFound(): void
    {
        $this->snapshotStore->expects(self::once())
            ->method('load')
            ->with('foo')
            ->willThrowException(new SnapshotNotFoundException())
        ;

        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $this->eventSourcedRepository->expects(self::once())
            ->method('load')
            ->with('foo')
            ->willReturn($aggregateRoot)
        ;

        self::assertSame($aggregateRoot, $this->repository->load('foo'));
    }

    public function testLoad(): void
    {
        $aggregateRoot = $this->createMock(EventSourcedAggregateRoot::class);
        $aggregateRoot->expects(self::once())
            ->method('getPlayhead')
            ->willReturn(51)
        ;

        $this->snapshotStore->expects(self::once())
            ->method('load')
            ->with('foo')
            ->willReturn($aggregateRoot)
        ;

        $events = [new StubEvent(1), new StubEvent(2)];
        $this->eventStore->expects(self::once())
            ->method('loadFromPlayhead')
            ->with('foo', 52)
            ->willReturn($events)
        ;

        $instances = [$aggregateRoot];
        $counter = \count($events);
        for ($i = 0; $i < $counter; ++$i) {
            $instances[$i + 1] = $this->createMock(EventSourcedAggregateRoot::class);
            $instances[$i]->expects(self::once())
                ->method('apply')
                ->with($events[$i])
                ->willReturn($instances[$i + 1])
            ;
        }

        self::assertSame(end($instances), $this->repository->load('foo'));
    }

    #[DataProvider('provideSaveCases')]
    public function testSave(bool $mustSnapshot): void
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();
        [$aggregateRoot, $event] = $aggregateRoot->stub(1);

        $commandResponse = new EventSourcedCommandResponse('foo', $event, $aggregateRoot->getPlayhead(), 'foomain', $aggregateRoot);

        $this->strategist->expects(self::once())
            ->method('mustSnapshot')
            ->with($aggregateRoot)
            ->willReturn($mustSnapshot)
        ;

        $this->snapshotStore->expects($mustSnapshot ? self::once() : self::never())
            ->method('snapshot')
            ->with($aggregateRoot)
        ;

        $this->eventSourcedRepository->expects(self::once())
            ->method('save')
            ->with($commandResponse)
        ;

        $this->repository->save($commandResponse);
    }

    /**
     * @return array<int, array<bool>>
     */
    public static function provideSaveCases(): iterable
    {
        return [
            [true],
            [false],
        ];
    }
}
