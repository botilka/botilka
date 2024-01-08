<?php

declare(strict_types=1);

namespace Botilka\Tests\Domain;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Tests\Fixtures\Domain\StubEventSourcedAggregateRoot;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(EventSourcedAggregateRoot::class)]
final class EventSourcedAggregateRootApplierTest extends TestCase
{
    private StubEventSourcedAggregateRoot $eventSourcedAggregateRoot;

    protected function setUp(): void
    {
        $this->eventSourcedAggregateRoot = new StubEventSourcedAggregateRoot();
    }

    public function testGetPlayhead(): void
    {
        self::assertSame(-1, $this->eventSourcedAggregateRoot->getPlayhead());
    }

    public function testApply(): void
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $result = $aggregateRoot->apply(new StubEvent(321));

        self::assertInstanceOf(StubEventSourcedAggregateRoot::class, $result);
        self::assertSame(0, $result->getPlayhead());
        self::assertSame(321, $result->getFoo());
    }
}
