<?php

declare(strict_types=1);

namespace Botilka\Tests\Application\Command;

use Botilka\Application\Command\EventSourcedCommandResponse;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Tests\Fixtures\Domain\StubEventSourcedAggregateRoot;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(EventSourcedCommandResponse::class)]
final class EventSourcedCommandResponseTest extends TestCase
{
    private EventSourcedCommandResponse $commandResponse;
    private StubEvent $event;
    private StubEventSourcedAggregateRoot $aggregateRoot;

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

    #[DataProvider('provideFromEventSourcedAggregateRootCases')]
    public function testFromEventSourcedAggregateRoot(?string $domain): void
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();
        $commandResponse = EventSourcedCommandResponse::fromEventSourcedAggregateRoot($aggregateRoot, $this->event, $domain);
        self::assertInstanceOf(EventSourcedCommandResponse::class, $commandResponse);
        self::assertSame($aggregateRoot->getAggregateRootId(), $commandResponse->getId());
        self::assertSame($aggregateRoot->getPlayhead(), $commandResponse->getPlayhead());
        self::assertSame($this->event, $commandResponse->getEvent());
        self::assertSame($domain ?? $aggregateRoot::class, $commandResponse->getDomain());
        self::assertSame($aggregateRoot, $commandResponse->getAggregateRoot());
    }

    /**
     * @return iterable<class-string|null>
     */
    public static function provideFromEventSourcedAggregateRootCases(): iterable
    {
        return [
            [null],
            ['FooBar\\Domain'],
        ];
    }
}
