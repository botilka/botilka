<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\Infrastructure\MongoDB\EventStoreManagerMongoDB;
use Botilka\Tests\AbstractKernelTestCase;
use Botilka\Tests\Fixtures\Application\EventStore\EventStoreMongoDBSetup;
use MongoDB\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @internal
 */
final class EventStoreManagerMongoDBTest extends AbstractKernelTestCase
{
    use EventStoreMongoDBSetup;

    #[DataProvider('provideLoadByAggregateRootIdFunctionalCases')]
    #[Group('functional')]
    public function testLoadByAggregateRootIdFunctional(int $shouldBeCount, string $id, int $from = null, int $to = null): void
    {
        [$eventStore, $collection] = $this->setUpEventStore();

        /** @var DenormalizerInterface $denormalizer */
        $denormalizer = self::$container->get('serializer');
        $manager = new EventStoreManagerMongoDB($collection, $denormalizer);

        $events = $manager->loadByAggregateRootId($id, $from, $to);

        self::assertCount($shouldBeCount, $events);
    }

    public static function provideLoadByAggregateRootIdFunctionalCases(): iterable
    {
        return [
            [10, 'foo', null, null],
            [6, 'foo', 4, null],
            [5, 'foo', 4, 8],
            [5, 'bar', null, null],
            [1, 'bar', 4, null],
            [2, 'bar', 3, 8],
        ];
    }

    #[DataProvider('provideLoadByDomainFunctionalCases')]
    #[Group('functional')]
    public function testLoadByDomainFunctional(int $shouldBeCount, string $domain): void
    {
        [$eventStore, $collection] = self::setUpEventStore();

        /** @var DenormalizerInterface $denormalizer */
        $denormalizer = self::$container->get('serializer');
        $manager = new EventStoreManagerMongoDB($collection, $denormalizer);

        $events = $manager->loadByDomain($domain);

        self::assertCount($shouldBeCount, $events);
    }

    public static function provideLoadByDomainFunctionalCases(): iterable
    {
        return [
            [15, 'FooBar\\Domain'],
            [15, 'FazBaz\\Domain'],
            [0, 'Non\\Existent'],
        ];
    }

    #[DataProvider('provideGetCases')]
    public function testGet(string $key, string $method): void
    {
        $collection = $this->createMock(Collection::class);
        $denormalizer = $this->createMock(DenormalizerInterface::class);

        $expected = ['bar', 'bar'];

        $collection->expects(self::once())
            ->method('distinct')
            ->with($key)
            ->willReturn($expected)
        ;

        $manager = new EventStoreManagerMongoDB($collection, $denormalizer);

        self::assertSame($expected, $manager->{$method}());
    }

    public static function provideGetCases(): iterable
    {
        return [
            ['id', 'getAggregateRootIds'],
            ['domain', 'getDomains'],
        ];
    }
}
