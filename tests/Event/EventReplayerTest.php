<?php

namespace Botilka\Tests\Event;

use Botilka\Event\EventReplayer;
use Botilka\Event\EventDispatcherInterface;
use Botilka\Event\EventReplayerInterface;
use Botilka\EventStore\EventStore;
use Botilka\Tests\Domain\StubEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EventReplayerTest extends TestCase
{
    /** @var EventStore|MockObject */
    private $eventStore;
    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcher;
    /** @var EventReplayerInterface */
    private $eventReplayer;

    public function setUp()
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->eventStore = $this->createMock(EventStore::class);
        $this->eventReplayer = new EventReplayer($this->eventStore, $this->eventDispatcher);
    }

    /** @dataProvider replayProvider */
    public function testReplay(string $id, ?int $fromPlayhead, ?int $toPlayhead, array $expectedArguments, string $expectedMethod, array $events)
    {
        $this->eventStore->expects($this->once())
            ->method($expectedMethod)
            ->with(...$expectedArguments)
            ->willReturn($events);

        $this->addEventsDispatchedExpectation($events);
        $this->eventReplayer->replay($id, $fromPlayhead, $toPlayhead);
    }

    public function replayProvider(): array
    {
        $events = [new StubEvent(1), new StubEvent(2)];

        return [
            ['foo', null, null, ['foo'], 'load', $events],
            ['foo', 10, null, ['foo', 10], 'loadFromPlayhead', $events],
            ['foo', 10, 20, ['foo', 10, 20],  'loadFromPlayheadToPlayhead', $events],
        ];
    }

    public function testReplayEvents()
    {
        $events = [new StubEvent(1), new StubEvent(2)];
        $this->addEventsDispatchedExpectation($events);
        $this->eventReplayer->replayEvents($events);
    }

    private function addEventsDispatchedExpectation(array $events): void
    {
        $this->eventDispatcher->expects($this->exactly(\count($events)))
            ->method('dispatch')
            ->withConsecutive(...$events);
    }
}
