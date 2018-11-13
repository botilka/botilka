<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\MongoDB;

use Botilka\Tests\AbstractKernelTestCase;
use Botilka\Tests\Fixtures\Domain\StubEvent;

final class EventStoreMongoDBTest extends AbstractKernelTestCase
{
    /** @group functionnal */
    public function testLoadFunctionnal(): void
    {
        self::setUpMongoDb();
        $this->assertCount(5, static::$eventStore->load('foo'));
        $this->assertCount(1, static::$eventStore->load('bar'));
    }

    /** @group functionnal */
    public function testLoadFromPlayheadToPlayheadFunctionnal(): void
    {
        self::setUpMongoDb();
        $this->assertCount(1, static::$eventStore->loadFromPlayheadToPlayhead('foo', 2, 3));
        $this->assertCount(1, static::$eventStore->loadFromPlayheadToPlayhead('foo', 4, 10));
    }

    /** @group functionnal */
    public function testLoadFromPlayheadFunctionnal(): void
    {
        self::setUpMongoDb();
        $this->assertCount(3, static::$eventStore->loadFromPlayhead('foo', 2));
        $this->assertCount(1, static::$eventStore->loadFromPlayhead('foo', 4));
    }

    /**
     * @group functionnal
     * @expectedException \Botilka\EventStore\EventStoreConcurrencyException
     * @expectedExceptionMessage Duplicate storage of event "Botilka\Tests\Fixtures\Domain\StubEvent" on aggregate "bar" with playhead 1.
     */
    public function testAppendBulkWriteExceptionFunctionnal(): void
    {
        self::setUpMongoDb();
        static::$eventStore->append('bar', 1, StubEvent::class, new StubEvent(42), null, new \DateTimeImmutable());
    }
}
