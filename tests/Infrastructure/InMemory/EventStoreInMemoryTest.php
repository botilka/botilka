<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\InMemory;

use Botilka\Infrastructure\InMemory\EventStoreInMemory;
use Botilka\Tests\Fixtures\Domain\EventStoreInMemoryFactory;
use PHPUnit\Framework\TestCase;

final class EventStoreInMemoryTest extends TestCase
{
    /** @var EventStoreInMemory */
    private $eventStore;

    protected function setUp()
    {
        $this->eventStore = EventStoreInMemoryFactory::create();
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
