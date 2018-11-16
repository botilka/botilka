<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\Infrastructure\MongoDB\EventStoreManagerMongoDB;
use Botilka\Tests\AbstractKernelTestCase;
use MongoDB\Collection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class EventStoreManagerMongoDBTest extends AbstractKernelTestCase
{
    /**
     * @dataProvider loadProviderFunctional
     * @group functional
     */
    public function testLoadFunctional(int $shouldBeCount, string $id, ?int $from = null, ?int $to = null): void
    {
        [$eventStore, $collection] = self::setUpMongoDb();

        /** @var DenormalizerInterface $denormalizer */
        $denormalizer = self::$container->get('serializer');
        $manager = new EventStoreManagerMongoDB($collection, $denormalizer);

        $events = $manager->load($id, $from, $to);

        $this->assertCount($shouldBeCount, $events);
    }

    public function loadProviderFunctional(): array
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

    public function testGetAggregateRootIds(): void
    {
        $collection = $this->createMock(Collection::class);
        $denormalizer = $this->createMock(DenormalizerInterface::class);

        $expected = ['bar', 'bar'];

        $collection->expects($this->once())
            ->method('distinct')
            ->with('id')
            ->willReturn($expected);

        $manager = new EventStoreManagerMongoDB($collection, $denormalizer);

        $this->assertSame($expected, $manager->getAggregateRootIds());
    }
}
