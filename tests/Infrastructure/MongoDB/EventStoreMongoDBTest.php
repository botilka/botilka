<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\Infrastructure\MongoDB\EventStoreMongoDB;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use MongoDB\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class EventStoreMongoDBTest extends TestCase
{
    /** @var EventStoreMongoDB */
    private $eventStore;

    /** @var Collection|MockObject */
    private $collection;

    /** @var NormalizerInterface|MockObject */
    private $normalizer;

    /** @var DenormalizerInterface|MockObject */
    private $denormalizer;

    protected function setUp(): void
    {
        $this->collection = $this->createMock(Collection::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->eventStore = new EventStoreMongoDB($this->collection, $this->normalizer, $this->denormalizer);
    }

    public function testAppend(): void
    {
        $this->collection->expects(self::once())->method('insertOne');

        $this->eventStore->append('bar', 1, StubEvent::class, new StubEvent(42), null, new \DateTimeImmutable(), 'Foo\\Domain');
    }
}
