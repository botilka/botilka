<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\EventStore\EventStore;
use Botilka\Tests\AbstractKernelTestCase;
use Botilka\Tests\Fixtures\Application\EventStore\EventStoreMongoDBSetup;
use Botilka\Tests\Fixtures\Domain\StubEvent;

final class EventStoreMongoDBTest extends AbstractKernelTestCase
{
    use EventStoreMongoDBSetup;

    public static function setUpBeforeClass()
    {
        [$eventStore, $collection] = self::setUpEventStore();
        self::$eventStore = $eventStore;
    }

    /**
     * @dataProvider loadFunctionalProvider
     * @group functional
     */
    public function testLoadFunctional(int $expectedCount, string $id): void
    {
        /** @var EventStore $eventStore */
        $eventStore = static::$eventStore;
        $this->assertCount($expectedCount, $eventStore->load($id));
    }

    public function loadFunctionalProvider(): array
    {
        return [
            [10, 'foo'],
            [5, 'bar'],
        ];
    }

    /**
     * @expectedException \Botilka\EventStore\AggregateRootNotFoundException
     * @expectedExceptionMessage No aggregrate root found for non_existent.
     * @group functional
     */
    public function testLoadNotFoundFunctional(): void
    {
        /** @var EventStore $eventStore */
        $eventStore = static::$eventStore;
        $eventStore->load('non_existent');
    }

    /**
     * @dataProvider loadFromPlayheadFunctionalProvider
     * @group functional
     */
    public function testLoadFromPlayheadFunctional(int $expectedCount, string $id, int $fromPlayhead): void
    {
        /** @var EventStore $eventStore */
        $eventStore = static::$eventStore;
        $this->assertCount($expectedCount, $eventStore->loadFromPlayhead($id, $fromPlayhead));
    }

    public function loadFromPlayheadFunctionalProvider(): array
    {
        return [
            [8, 'foo', 2],
            [6, 'foo', 4],
            [3, 'bar', 2],
            [1, 'bar', 4],
        ];
    }

    /**
     * @expectedException \Botilka\EventStore\AggregateRootNotFoundException
     * @expectedExceptionMessage No aggregrate root found for non_existent from playhead 2.
     * @group functional
     */
    public function testLoadFromPlayheadNotFoundFunctional(): void
    {
        /** @var EventStore $eventStore */
        $eventStore = static::$eventStore;
        $eventStore->loadFromPlayhead('non_existent', 2);
    }

    /**
     * @dataProvider loadFromPlayheadToPlayheadFunctionalProvider
     * @group functional
     */
    public function testLoadFromPlayheadToPlayheadFunctional(int $expectedCount, string $id, int $fromPlayhead, int $toPlayhead): void
    {
        /** @var EventStore $eventStore */
        $eventStore = static::$eventStore;
        $this->assertCount($expectedCount, $eventStore->loadFromPlayheadToPlayhead($id, $fromPlayhead, $toPlayhead));
    }

    public function loadFromPlayheadToPlayheadFunctionalProvider(): array
    {
        return [
            [2, 'foo', 2, 3],
            [6, 'foo', 4, 10],
            [3, 'bar', 2, 10],
            [3, 'bar', 2, 4],
        ];
    }

    /**
     * @expectedException \Botilka\EventStore\AggregateRootNotFoundException
     * @expectedExceptionMessage No aggregrate root found for non_existent from playhead 2 to playhead 4.
     * @group functional
     */
    public function testLoadFromPlayheadToPlayheadNotFoundFunctional(): void
    {
        /** @var EventStore $eventStore */
        $eventStore = static::$eventStore;
        $eventStore->loadFromPlayheadToPlayhead('non_existent', 2, 4);
    }

    /**
     * @group functional
     * @expectedException \Botilka\EventStore\EventStoreConcurrencyException
     * @expectedExceptionMessage Duplicate storage of event "Botilka\Tests\Fixtures\Domain\StubEvent" on aggregate "bar" with playhead 1.
     */
    public function testAppendBulkWriteExceptionFunctional(): void
    {
        /** @var EventStore $eventStore */
        $eventStore = static::$eventStore;
        $eventStore->append('bar', 1, StubEvent::class, new StubEvent(42), null, new \DateTimeImmutable(), 'Foo\\Domain');
    }
}
