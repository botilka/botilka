<?php

declare(strict_types=1);

namespace Botilka\Tests\Event;

use Botilka\Event\DefaultEventReplayer;
use Botilka\Event\EventDispatcher;
use Botilka\Event\EventReplayer;
use Botilka\EventStore\EventStore;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DefaultEventReplayerTest extends TestCase
{
    /** @var EventStore|MockObject */
    private $eventStore;
    /** @var EventDispatcher|MockObject */
    private $eventDispatcher;
    /** @var EventReplayer */
    private $eventReplayer;

    public function setUp()
    {
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->eventStore = $this->createMock(EventStore::class);
        $this->eventReplayer = new DefaultEventReplayer($this->eventStore, $this->eventDispatcher);
    }

    /** @dataProvider replayProvider */
    public function testReplay(string $id, ?int $fromPlayhead, ?int $toPlayhead, array $expectedArguments, string $expectedMethod, array $events): void
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
            ['bar', 10, null, ['bar', 10], 'loadFromPlayhead', $events],
            ['baz', 10, 20, ['baz', 10, 20], 'loadFromPlayheadToPlayhead', $events],
        ];
    }

    public function testReplayEvents(): void
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
