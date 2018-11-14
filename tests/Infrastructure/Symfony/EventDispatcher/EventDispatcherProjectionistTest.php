<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Symfony\EventDispatcher;

use Botilka\Event\Event;
use Botilka\Infrastructure\Symfony\EventDispatcher\EventDispatcherProjectionist;
use Botilka\Projector\Projection;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Tests\Fixtures\Domain\StubProjector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcherProjectionistTest extends TestCase
{
    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcher;
    /** @var LoggerInterface|MockObject */
    private $logger;

    public function setUp()
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testReplay()
    {
        $event = new StubEvent(42);

        $this->logger->expects($this->never())
            ->method('notice');

        $projector = new StubProjector();
        $this->assertFalse($projector->onStubEventPlayed);

        $projectionist = new EventDispatcherProjectionist($this->eventDispatcher, [$projector], $this->logger);

        $projection = $this->createMock(Projection::class);
        $projection->expects($this->once())->method('getEvent')
            ->willReturn($event);

        $projectionist->replay($projection);
        $this->assertTrue($projector->onStubEventPlayed);
    }

    public function testReplayNoHandler()
    {
        $event = $this->createMock(Event::class);

        $this->logger->expects($this->once())
            ->method('notice')
            ->with(\sprintf('No projector handler for %s.', \get_class($event)));

        $projectionist = new EventDispatcherProjectionist($this->eventDispatcher, [new StubProjector()], $this->logger);

        $projection = $this->createMock(Projection::class);
        $projection->expects($this->once())->method('getEvent')
            ->willReturn($event);

        $projectionist->replay($projection);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame([
            Projection::class => 'replay',
        ], EventDispatcherProjectionist::getSubscribedEvents());
    }
}
