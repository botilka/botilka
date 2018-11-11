<?php

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\Infrastructure\MongoDB\EventStoreMongoDB;
use MongoDB\Collection;
use MongoDB\Driver\Cursor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventStoreMongoDBTest extends TestCase
{
    /** @var EventStoreMongoDB */
    private $eventStore;
    /** @var Collection|MockObject */
    private $collection;
    /** @var NormalizerInterface|MockObject */
    private $normalizer;
    /** @var DenormalizerInterface|MockObject */
    private $denormalizer;

    /** @var Cursor|MockObject */
    private $cursor;

    protected function setUp()
    {

        $this->collection = $this->createMock(Collection::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);

        $this->eventStore = new EventStoreMongoDB($this->collection, $this->normalizer, $this->denormalizer);

        $reflectionClass = new \ReflectionClass(Cursor::class);
        $constructor = $reflectionClass->getConstructor();
        $constructor->setAccessible(true);


        $this->cursor = $reflectionClass->newInstance([
            ['type' => 'Foo\\Bar', 'payload' => \json_encode(['foo' => 'bar'])],
        ]);

        $this->collection->expects($this->once())
            ->method('find')
            ->willReturn($this->cursor);

    }

    public function testLoad()
    {

        $this->eventStore->load(1);

    }

    public function testAppend()
    {

    }

    public function testLoadFromPlayheadToPlayhead()
    {

    }


    public function testLoadFromPlayhead()
    {

    }


    /**
     * @param MockObject|Collection $collection
     */
    private function addDenormalizerExpectation(MockObject $collection): void
    {
        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(['foo' => 'bar'], 'Foo\\Bar')
            ->willReturn('baz');
    }
}
