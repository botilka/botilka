<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\InMemory;

use Botilka\Infrastructure\InMemory\EventStoreManagerInMemory;
use Botilka\Tests\Fixtures\Domain\EventStoreInMemoryFactory;
use PHPUnit\Framework\TestCase;

final class EventStoreManagerInMemoryTest extends TestCase
{
    /** @dataProvider loadByAggregateRootIdProvider */
    public function testLoadByAggregateRootIdProvider(int $expectedCount, string $id, ?int $from = null, ?int $to = null): void
    {
        $eventStore = EventStoreInMemoryFactory::create();
        $manager = new EventStoreManagerInMemory($eventStore);

        $this->assertCount($expectedCount, $manager->loadByAggregateRootId($id, $from, $to));
    }

    public function loadByAggregateRootIdProvider(): array
    {
        return [
            [10, 'foo', null, null],
            [6, 'foo', 4, null],
            [4, 'foo', 4, 8],
            [5, 'bar', null, null],
            [1, 'bar', 4, null],
            [2, 'bar', 3, 8],
        ];
    }

    /** @dataProvider loadByDomainProvider */
    public function testLoadByDomain(int $shouldBeCount, string $domain): void
    {
        $eventStore = EventStoreInMemoryFactory::create();
        $manager = new EventStoreManagerInMemory($eventStore);

        $this->assertCount($shouldBeCount, $manager->loadByDomain($domain));
    }

    public function loadByDomainProvider(): array
    {
        return [
            [15, 'FooBar\\Domain'],
            [15, 'FazBaz\\Domain'],
            [0, 'Non\\Existent'],
        ];
    }

    public function testGetAggregateRootIds(): void
    {
        $eventStore = EventStoreInMemoryFactory::create();
        $manager = new EventStoreManagerInMemory($eventStore);

        $this->assertSame(['foo', 'bar', 'faz', 'baz'], $manager->getAggregateRootIds());
    }

    public function testGetDomains(): void
    {
        $eventStore = EventStoreInMemoryFactory::create();
        $manager = new EventStoreManagerInMemory($eventStore);

        $this->assertSame(['FooBar\\Domain', 'FazBaz\\Domain'], $manager->getDomains());
    }
}
