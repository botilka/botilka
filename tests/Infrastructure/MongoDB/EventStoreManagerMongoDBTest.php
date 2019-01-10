<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\Infrastructure\MongoDB\EventStoreManagerMongoDB;
use Botilka\Tests\AbstractKernelTestCase;
use Botilka\Tests\Fixtures\Application\EventStore\EventStoreMongoDBSetup;
use MongoDB\Collection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class EventStoreManagerMongoDBTest extends AbstractKernelTestCase
{
    use EventStoreMongoDBSetup;

    /**
     * @dataProvider loadByAggregateRootIdProviderFunctional
     * @group functional
     */
    public function testLoadByAggregateRootIdFunctional(int $shouldBeCount, string $id, ?int $from = null, ?int $to = null): void
    {
        [$eventStore, $collection] = $this->setUpEventStore();

        /** @var DenormalizerInterface $denormalizer */
        $denormalizer = self::$container->get('serializer');
        $manager = new EventStoreManagerMongoDB($collection, $denormalizer);

        $events = $manager->loadByAggregateRootId($id, $from, $to);

        $this->assertCount($shouldBeCount, $events);
    }

    public function loadByAggregateRootIdProviderFunctional(): array
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

    /**
     * @dataProvider loadByDomainFunctionalProvider
     * @group functional
     */
    public function testLoadByDomainFunctional(int $shouldBeCount, string $domain): void
    {
        [$eventStore, $collection] = self::setUpEventStore();

        /** @var DenormalizerInterface $denormalizer */
        $denormalizer = self::$container->get('serializer');
        $manager = new EventStoreManagerMongoDB($collection, $denormalizer);

        $events = $manager->loadByDomain($domain);

        $this->assertCount($shouldBeCount, $events);
    }

    public function loadByDomainFunctionalProvider(): array
    {
        return [
            [15, 'FooBar\\Domain'],
            [15, 'FazBaz\\Domain'],
            [0, 'Non\\Existent'],
        ];
    }

    /** @dataProvider getProvider */
    public function testGet(string $key, string $method): void
    {
        $collection = $this->createMock(Collection::class);
        $denormalizer = $this->createMock(DenormalizerInterface::class);

        $expected = ['bar', 'bar'];

        $collection->expects($this->once())
            ->method('distinct')
            ->with($key)
            ->willReturn($expected);

        $manager = new EventStoreManagerMongoDB($collection, $denormalizer);

        $this->assertSame($expected, $manager->$method());
    }

    public function getProvider(): array
    {
        return [
            ['id', 'getAggregateRootIds'],
            ['domain', 'getDomains'],
        ];
    }
}
