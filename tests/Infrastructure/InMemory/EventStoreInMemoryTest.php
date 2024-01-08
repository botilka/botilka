<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\InMemory;

use Botilka\Infrastructure\InMemory\EventStoreInMemory;
use Botilka\Tests\Fixtures\Domain\EventStoreInMemoryFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(EventStoreInMemory::class)]
final class EventStoreInMemoryTest extends TestCase
{
    private EventStoreInMemory $eventStore;

    protected function setUp(): void
    {
        $this->eventStore = EventStoreInMemoryFactory::create();
    }

    public function testLoad(): void
    {
        self::assertCount(10, $this->eventStore->load('foo'));
    }

    public function testLoadFromPlayhead(): void
    {
        self::assertCount(3, $this->eventStore->loadFromPlayhead('foo', 7));
    }

    public function testLoadFromPlayheadToPlayhead(): void
    {
        self::assertCount(4, $this->eventStore->loadFromPlayheadToPlayhead('foo', 4, 8));
    }
}
