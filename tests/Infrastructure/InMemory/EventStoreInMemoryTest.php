<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\InMemory;

use Botilka\Infrastructure\InMemory\EventStoreInMemory;
use Botilka\Tests\Fixtures\Domain\StubEvent;
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

    public function testLoad(): void
    {
        $this->assertCount(10, $this->eventStore->load('foo'));
    }

    public function testLoadFromPlayhead(): void
    {
        $this->assertCount(3, $this->eventStore->loadFromPlayhead('foo', 7));
    }

    public function testLoadFromPlayheadToPlayhead(): void
    {
        $this->assertCount(4, $this->eventStore->loadFromPlayheadToPlayhead('foo', 4, 8));
    }
}
