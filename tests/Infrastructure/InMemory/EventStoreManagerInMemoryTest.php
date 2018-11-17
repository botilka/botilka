<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\InMemory;

use Botilka\Infrastructure\InMemory\EventStoreManagerInMemory;
use Botilka\Tests\Fixtures\Domain\EventStoreInMemoryFactory;
use PHPUnit\Framework\TestCase;

final class EventStoreManagerInMemoryTest extends TestCase
{
    /** @dataProvider loadByAggregateRootIdProvider */
    public function testLoadByAggregateRootIdProvider(string $id, ?int $from = null, ?int $to = null, int $shouldBeCount): void
    {
        $eventStore = EventStoreInMemoryFactory::create();
        $manager = new EventStoreManagerInMemory($eventStore);

        $this->assertCount($shouldBeCount, $manager->loadByAggregateRootId($id, $from, $to));
    }

    public function loadByAggregateRootIdProvider(): array
    {
        return [
            ['foo', null, null, 10],
            ['foo', 4, null, 6],
            ['foo', 4, 8, 4],
            ['bar', null, null, 5],
            ['bar', 4, null, 1],
            ['bar', 3, 8, 2],
        ];
    }

    public function testGetAggregateRootIds(): void
    {
        $eventStore = EventStoreInMemoryFactory::create();
        $manager = new EventStoreManagerInMemory($eventStore);

        $this->assertSame(['foo', 'bar'], $manager->getAggregateRootIds());
    }
}
