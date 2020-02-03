<?php

declare(strict_types=1);

namespace Botilka\Tests\EventStore;

use Botilka\EventStore\ManagedEvent;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;

final class ManagedEventTest extends TestCase
{
    /** @var ManagedEvent */
    private $managedEvent;
    /** @var StubEvent */
    private $domainEvent;
    /** @var \DateTimeImmutable */
    private $recordedOn;

    protected function setUp(): void
    {
        $this->domainEvent = new StubEvent(42);
        $this->recordedOn = new \DateTimeImmutable();
        $this->managedEvent = new ManagedEvent('foo', $this->domainEvent, 1337, ['foo' => 'bar'], $this->recordedOn, 'Foo\\Domain');
    }

    public function testGetId(): void
    {
        self::assertSame('foo', $this->managedEvent->getId());
    }

    public function testGetRecordedOn(): void
    {
        self::assertSame($this->recordedOn, $this->managedEvent->getRecordedOn());
    }

    public function testGetMetadata(): void
    {
        self::assertSame(['foo' => 'bar'], $this->managedEvent->getMetadata());
    }

    public function testGetPlayhead(): void
    {
        self::assertSame(1337, $this->managedEvent->getPlayhead());
    }

    public function testGetDomainEvent(): void
    {
        self::assertSame($this->domainEvent, $this->managedEvent->getDomainEvent());
    }

    public function testGetDomain(): void
    {
        self::assertSame('Foo\\Domain', $this->managedEvent->getDomain());
    }
}
