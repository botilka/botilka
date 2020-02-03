<?php

declare(strict_types=1);

namespace Botilka\Tests\Domain;

use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Tests\Fixtures\Domain\StubEventSourcedAggregateRoot;
use PHPUnit\Framework\TestCase;

final class EventSourcedAggregateRootApplierTest extends TestCase
{
    /** @var StubEventSourcedAggregateRoot */
    private $eventSourcedAggregateRoot;

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
