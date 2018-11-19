<?php

declare(strict_types=1);

namespace Botilka\Tests\Application\Command;

use Botilka\Application\Command\EventSourcedCommandResponse;
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

    protected function setUp()
    {
        $this->event = new StubEvent(123);
        $aggregateRoot = new StubEventSourcedAggregateRoot();
        $this->commandResponse = new EventSourcedCommandResponse('foo', $this->event, 456, 'bar');
    }

    public function testGetPlayhead(): void
    {
        $this->assertSame(456, $this->commandResponse->getPlayhead());
    }

    public function testGetEvent(): void
    {
        $this->assertSame($this->event, $this->commandResponse->getEvent());
    }

    public function testGetId(): void
    {
        $this->assertSame('foo', $this->commandResponse->getId());
    }

    public function testGetDomain(): void
    {
        $this->assertSame('bar', $this->commandResponse->getDomain());
    }

    /** @dataProvider fromEventSourcedAggregateRootProvider */
    public function testFromEventSourcedAggregateRoot(?string $domain): void
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();
        $commandResponse = EventSourcedCommandResponse::fromEventSourcedAggregateRoot($aggregateRoot, $this->event, $domain);
        $this->assertInstanceOf(EventSourcedCommandResponse::class, $commandResponse);
        $this->assertSame($aggregateRoot->getAggregateRootId(), $commandResponse->getId());
        $this->assertSame($aggregateRoot->getPlayhead(), $commandResponse->getPlayhead());
        $this->assertSame($this->event, $commandResponse->getEvent());
        $this->assertSame($domain ?? \get_class($aggregateRoot), $commandResponse->getDomain());
    }

    public function fromEventSourcedAggregateRootProvider(): array
    {
        return [
            [null],
            ['FooBar\\Domain'],
        ];
    }
}
