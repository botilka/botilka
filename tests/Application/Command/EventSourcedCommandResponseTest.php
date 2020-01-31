<?php

declare(strict_types=1);

namespace Botilka\Tests\Application\Command;

use Botilka\Application\Command\EventSourcedCommandResponse;
use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Event\Event;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Tests\Fixtures\Domain\StubEventSourcedAggregateRoot;
use PHPUnit\Framework\TestCase;

final class EventSourcedCommandResponseTest extends TestCase
{
    /** @var EventSourcedCommandResponse */
    private $commandResponse;
    /** @var Event */
    private $event;
    /** @var EventSourcedAggregateRoot */
    private $aggregateRoot;

    protected function setUp(): void
    {
        $this->event = new StubEvent(123);
        $this->aggregateRoot = new StubEventSourcedAggregateRoot();
        $this->commandResponse = new EventSourcedCommandResponse('foo', $this->event, 456, 'bar', $this->aggregateRoot);
    }

    public function testGetPlayhead(): void
    {
        self::assertSame(456, $this->commandResponse->getPlayhead());
    }

    public function testGetEvent(): void
    {
        self::assertSame($this->event, $this->commandResponse->getEvent());
    }

    public function testGetId(): void
    {
        self::assertSame('foo', $this->commandResponse->getId());
    }

    public function testGetDomain(): void
    {
        self::assertSame('bar', $this->commandResponse->getDomain());
    }

    public function testAggregateRoot(): void
    {
        self::assertSame($this->aggregateRoot, $this->commandResponse->getAggregateRoot());
    }

    /** @dataProvider fromEventSourcedAggregateRootProvider */
    public function testFromEventSourcedAggregateRoot(?string $domain): void
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();
        $commandResponse = EventSourcedCommandResponse::fromEventSourcedAggregateRoot($aggregateRoot, $this->event, $domain);
        self::assertInstanceOf(EventSourcedCommandResponse::class, $commandResponse);
        self::assertSame($aggregateRoot->getAggregateRootId(), $commandResponse->getId());
        self::assertSame($aggregateRoot->getPlayhead(), $commandResponse->getPlayhead());
        self::assertSame($this->event, $commandResponse->getEvent());
        self::assertSame($domain ?? \get_class($aggregateRoot), $commandResponse->getDomain());
        self::assertSame($aggregateRoot, $commandResponse->getAggregateRoot());
    }

    public function fromEventSourcedAggregateRootProvider(): array
    {
        return [
            [null],
            ['FooBar\\Domain'],
        ];
    }
}
