<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\EventStore\AggregateRootNotFoundException;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Botilka\Tests\AbstractKernelTestCase;
use Botilka\Tests\Fixtures\Application\EventStore\EventStoreMongoDBSetup;
use Botilka\Tests\Fixtures\Domain\StubEvent;

final class EventStoreMongoDBFunctionnalTest extends AbstractKernelTestCase
{
    use EventStoreMongoDBSetup;

    /**
     * @dataProvider loadFunctionalProvider
     * @group functional
     */
    public function testLoadFunctional(int $expectedCount, string $id): void
    {
        /** @var EventStore $eventStore */
        [$eventStore, $collection] = $this->setUpEventStore();
        self::assertCount($expectedCount, $eventStore->load($id));
    }

    public function loadFunctionalProvider(): array
    {
        return [
            [10, 'foo'],
            [5, 'bar'],
        ];
    }

    /**
     * @group functional
     */
    public function testLoadNotFoundFunctional(): void
    {
        /** @var EventStore $eventStore */
        [$eventStore, $collection] = $this->setUpEventStore();

        $this->expectException(AggregateRootNotFoundException::class);
        $this->expectExceptionMessage('No aggregrate root found for non_existent.');

        $eventStore->load('non_existent');
    }

    /**
     * @dataProvider loadFromPlayheadFunctionalProvider
     * @group functional
     */
    public function testLoadFromPlayheadFunctional(int $expectedCount, string $id, int $fromPlayhead): void
    {
        /** @var EventStore $eventStore */
        [$eventStore, $collection] = $this->setUpEventStore();
        self::assertCount($expectedCount, $eventStore->loadFromPlayhead($id, $fromPlayhead));
    }

    public function loadFromPlayheadFunctionalProvider(): array
    {
        return [
            [8, 'foo', 2],
            [6, 'foo', 4],
            [3, 'bar', 2],
            [1, 'bar', 4],
            [0, 'bar', 1337],
        ];
    }

    /**
     * @dataProvider loadFromPlayheadToPlayheadFunctionalProvider
     * @group functional
     */
    public function testLoadFromPlayheadToPlayheadFunctional(int $expectedCount, string $id, int $fromPlayhead, int $toPlayhead): void
    {
        /** @var EventStore $eventStore */
        [$eventStore, $collection] = $this->setUpEventStore();
        self::assertCount($expectedCount, $eventStore->loadFromPlayheadToPlayhead($id, $fromPlayhead, $toPlayhead));
    }

    public function loadFromPlayheadToPlayheadFunctionalProvider(): array
    {
        return [
            [2, 'foo', 2, 3],
            [6, 'foo', 4, 10],
            [3, 'bar', 2, 10],
            [3, 'bar', 2, 4],
            [0, 'bar', 42, 51],
        ];
    }

    /**
     * @group functional
     */
    public function testAppendBulkWriteExceptionFunctional(): void
    {
        /** @var EventStore $eventStore */
        [$eventStore, $collection] = $this->setUpEventStore();

        $this->expectException(EventStoreConcurrencyException::class);
        $this->expectExceptionMessage('Duplicate storage of event "Botilka\Tests\Fixtures\Domain\StubEvent" on aggregate "bar" with playhead 1.');

        $eventStore->append('bar', 1, StubEvent::class, new StubEvent(42), null, new \DateTimeImmutable(), 'Foo\\Domain');
    }
}
