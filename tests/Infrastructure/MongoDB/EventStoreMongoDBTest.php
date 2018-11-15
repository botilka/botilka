<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\EventStore\EventStore;
use Botilka\Tests\AbstractKernelTestCase;
use Botilka\Tests\Fixtures\Domain\StubEvent;

final class EventStoreMongoDBTest extends AbstractKernelTestCase
{
    public static function setUpBeforeClass()
    {
        self::setUpMongoDb();
    }

    public static function tearDownAfterClass()
    {
        static::$eventStore = null;
    }

    /**
     * @dataProvider loadFunctionalProvider
     * @group functional
     */
    public function testLoadFunctional(int $expected, string $id): void
    {
        /** @var EventStore $eventStore */
        $eventStore = static::$eventStore;
        $this->assertCount($expected, $eventStore->load($id));
    }

    public function loadFunctionalProvider(): array
    {
        return [
            [10, 'foo'],
            [5, 'bar'],
        ];
    }

    /**
     * @dataProvider loadFromPlayheadToPlayheadFunctionalProvider
     * @group functional
     */
    public function testLoadFromPlayheadToPlayheadFunctional(int $expected, string $id, int $fromPlayhead, int $toPlayhead): void
    {
        /** @var EventStore $eventStore */
        $eventStore = static::$eventStore;
        $this->assertCount($expected, $eventStore->loadFromPlayheadToPlayhead($id, $fromPlayhead, $toPlayhead));
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
     * @dataProvider loadFromPlayheadFunctionalProvider
     * @group functional
     */
    public function testLoadFromPlayheadFunctional(int $expected, string $id, int $fromPlayhead): void
    {
        /** @var EventStore $eventStore */
        $eventStore = static::$eventStore;
        $this->assertCount($expected, $eventStore->loadFromPlayhead($id, $fromPlayhead));
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
     * @group functional
     * @expectedException \Botilka\EventStore\EventStoreConcurrencyException
     * @expectedExceptionMessage Duplicate storage of event "Botilka\Tests\Fixtures\Domain\StubEvent" on aggregate "bar" with playhead 1.
     */
    public function testAppendBulkWriteExceptionFunctional(): void
    {
        /** @var EventStore $eventStore */
        $eventStore = static::$eventStore;
        $eventStore->append('bar', 1, StubEvent::class, new StubEvent(42), null, new \DateTimeImmutable());
    }
}
