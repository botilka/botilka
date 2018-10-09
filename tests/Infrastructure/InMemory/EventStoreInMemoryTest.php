<?php

namespace Botilka\Tests\Infrastructure\InMemory;

use Botilka\Infrastructure\InMemory\EventStoreInMemory;
use Botilka\Tests\Domain\StubEvent;
use PHPUnit\Framework\TestCase;

final class EventStoreInMemoryTest extends TestCase
{
    /** @var EventStoreInMemory */
    private $eventStore;

    public function setUp()
    {
        $eventStore = new EventStoreInMemory();

        for ($i = 0; $i < 10; ++$i) {
            $event = new StubEvent($i * 100);
            $eventStore->append('foo', $i, StubEvent::class, $event, null, new \DateTimeImmutable());
        }

        for ($i = 0; $i < 10; ++$i) {
            $event = new StubEvent($i + 100);
            $eventStore->append('bar', $i, StubEvent::class, $event, null, new \DateTimeImmutable());
        }

        $this->eventStore = $eventStore;
    }

    public function testLoad()
    {
        $this->assertCount(10, $this->eventStore->load('foo'));
    }

    public function testLoadFromPlayhead()
    {
        $this->assertCount(5, $this->eventStore->loadFromPlayhead('foo', 5));
    }

    public function testLoadFromPlayheadToPlayhead()
    {
        $this->assertCount(5, $this->eventStore->loadFromPlayheadToPlayhead('foo', 4, 8));
    }
}
