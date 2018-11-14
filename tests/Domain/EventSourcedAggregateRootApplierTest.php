<?php

declare(strict_types=1);

namespace Botilka\Tests\Domain;

use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Tests\Fixtures\Domain\StubEventSourcedAggregateRoot;
use PHPUnit\Framework\TestCase;

final class EventSourcedAggregateRootApplierTest extends TestCase
{
    private $eventSourcedAggregateRoot;

    protected function setUp()
    {
        $this->eventSourcedAggregateRoot = new StubEventSourcedAggregateRoot();
    }

    public function testGetPlayhead(): void
    {
        $this->assertSame(-1, $this->eventSourcedAggregateRoot->getPlayhead());
    }

    public function testApply(): void
    {
        $aggregateRoot = new StubEventSourcedAggregateRoot();

        $result = $aggregateRoot->apply(new StubEvent(321));

        $this->assertInstanceOf(StubEventSourcedAggregateRoot::class, $result);
        $this->assertSame(0, $result->getPlayhead());
        $this->assertSame(321, $result->getFoo());
    }
}
