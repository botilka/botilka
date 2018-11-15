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

    protected function setUp()
    {
        $this->domainEvent = new StubEvent(42);
        $this->recordedOn = new \DateTimeImmutable();
        $this->managedEvent = new ManagedEvent('foo', $this->domainEvent, 1337, ['foo' => 'bar'], $this->recordedOn);
    }

    public function testGetId()
    {
        $this->assertSame('foo', $this->managedEvent->getId());
    }

    public function testGetRecordedOn(): void
    {
        $this->assertSame($this->recordedOn, $this->managedEvent->getRecordedOn());
    }

    public function testGetMetadata(): void
    {
        $this->assertSame(['foo' => 'bar'], $this->managedEvent->getMetadata());
    }

    public function testGetPlayhead(): void
    {
        $this->assertSame(1337, $this->managedEvent->getPlayhead());
    }

    public function testGetDomainEvent(): void
    {
        $this->assertSame($this->domainEvent, $this->managedEvent->getDomainEvent());
    }
}
