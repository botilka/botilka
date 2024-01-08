<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\InMemory;

use Botilka\Infrastructure\InMemory\EventStoreManagerInMemory;
use Botilka\Tests\Fixtures\Domain\EventStoreInMemoryFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(EventStoreManagerInMemory::class)]
final class EventStoreManagerInMemoryTest extends TestCase
{
    #[DataProvider('provideLoadByAggregateRootIdProviderCases')]
    public function testLoadByAggregateRootIdProvider(int $expectedCount, string $id, int $from = null, int $to = null): void
    {
        $eventStore = EventStoreInMemoryFactory::create();
        $manager = new EventStoreManagerInMemory($eventStore);

        self::assertCount($expectedCount, $manager->loadByAggregateRootId($id, $from, $to));
    }

    public static function provideLoadByAggregateRootIdProviderCases(): iterable
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

    #[DataProvider('provideLoadByDomainCases')]
    public function testLoadByDomain(int $shouldBeCount, string $domain): void
    {
        $eventStore = EventStoreInMemoryFactory::create();
        $manager = new EventStoreManagerInMemory($eventStore);

        self::assertCount($shouldBeCount, $manager->loadByDomain($domain));
    }

    public static function provideLoadByDomainCases(): iterable
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

        self::assertSame(['foo', 'bar', 'faz', 'baz'], $manager->getAggregateRootIds());
    }

    public function testGetDomains(): void
    {
        $eventStore = EventStoreInMemoryFactory::create();
        $manager = new EventStoreManagerInMemory($eventStore);

        self::assertSame(['FooBar\\Domain', 'FazBaz\\Domain'], $manager->getDomains());
    }
}
