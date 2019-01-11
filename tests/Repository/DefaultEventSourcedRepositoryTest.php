<?php

declare(strict_types=1);

namespace Botilka\Tests\Repository;

use Botilka\Application\Command\EventSourcedCommandResponse;
use Botilka\EventStore\EventStore;
use Botilka\Repository\DefaultEventSourcedRepository;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Tests\Fixtures\Domain\StubEventSourcedAggregateRoot;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DefaultEventSourcedRepositoryTest extends TestCase
{
    /** @var EventStore|MockObject */
    private $eventStore;
    /** @var string */
    private $aggregateRootClass;
    /** @var DefaultEventSourcedRepository */
    private $repository;

    protected function setUp()
    {
        $this->eventStore = $this->createMock(EventStore::class);
        $this->aggregateRootClass = StubEventSourcedAggregateRoot::class;
        $this->repository = new DefaultEventSourcedRepository($this->eventStore, $this->aggregateRootClass);
    }

    public function testLoad(): void
    {
        $events = [new StubEvent(1), new StubEvent(2)];
        $this->eventStore->expects($this->once())
            ->method('load')
            ->with('foo')
            ->willReturn($events);

        $aggregateRoot = $this->repository->load('foo');
        $this->assertSame(1, $aggregateRoot->getPlayhead());
    }

    public function testSave(): void
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();
        [$aggregateRoot, $event] = $aggregateRoot->stub(1);

        $commandResponse = new EventSourcedCommandResponse('foo', $event, $aggregateRoot->getPlayhead(), 'foomain', $aggregateRoot);

        $this->eventStore->expects($this->once())
            ->method('append')
            ->with('foo', 0, \get_class($event), $event, null, $this->isInstanceOf(\DateTimeImmutable::class), 'foomain');

        $this->repository->save($commandResponse);
    }
}
