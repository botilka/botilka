<?php

declare(strict_types=1);

namespace Botilka\Tests\Application\Command;

use Botilka\Application\Command\CommandResponse;
use Botilka\Event\Event;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Tests\Fixtures\Domain\StubEventSourcedAggregateRoot;
use PHPUnit\Framework\TestCase;

final class CommandResponseTest extends TestCase
{
    /** @var CommandResponse */
    private $commandResponse;
    /** @var Event */
    private $event;

    protected function setUp()
    {
        $this->event = new StubEvent(123);
        $aggregateRoot = new StubEventSourcedAggregateRoot();
        $this->commandResponse = new CommandResponse('foo', 456, $this->event, StubEventSourcedAggregateRoot::class);
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
        $this->assertSame(StubEventSourcedAggregateRoot::class, $this->commandResponse->getDomain());
    }

    public function testWithValue(): void
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();
        $commandResponse = CommandResponse::withValue($aggregateRoot, $this->event);
        $this->assertInstanceOf(CommandResponse::class, $commandResponse);
        $this->assertSame($aggregateRoot->getAggregateRootId(), $commandResponse->getId());
        $this->assertSame($aggregateRoot->getPlayhead(), $commandResponse->getPlayhead());
        $this->assertSame($this->event, $commandResponse->getEvent());
        $this->assertSame(StubEventSourcedAggregateRoot::class, $commandResponse->getDomain());
    }
}
