<?php

declare(strict_types=1);

namespace Botilka\Tests\EventStore;

use Botilka\EventStore\ManagedEvent;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;

class ManagedEventTest extends TestCase
{
    /** @var ManagedEvent */
    private $managedEvent;
    /** @var StubEvent */
    private $domainEvent;
    /** @var \DateTimeImmutable */
    private $recordedOn;

    public function setUp()
    {
        $this->domainEvent = new StubEvent(42);
        $this->recordedOn = new \DateTimeImmutable();
        $this->managedEvent = new ManagedEvent($this->domainEvent, 1337, ['foo' => 'bar'], $this->recordedOn);
    }

    public function testGetRecordedOn()
    {
        $this->assertSame($this->recordedOn, $this->managedEvent->getRecordedOn());
    }

    public function testGetMetadata()
    {
        $this->assertSame(['foo' => 'bar'], $this->managedEvent->getMetadata());
    }

    public function testGetPlayhead()
    {
        $this->assertSame(1337, $this->managedEvent->getPlayhead());
    }

    public function testGetDomainEvent()
    {
        $this->assertSame($this->domainEvent, $this->managedEvent->getDomainEvent());
    }
}
