<?php

namespace Botilka\Tests\Event;

use Botilka\Event\EventDispatcher;
use Botilka\Tests\Domain\StubEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBusInterface;

final class EventDispatcherTest extends TestCase
{
    public function testDispatchWithHandler()
    {
        $eventBus = $this->createMock(MessageBusInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $eventDispatcher = new EventDispatcher($eventBus, $logger);

        $event = new StubEvent(42);

        $eventBus->expects($this->once())
            ->method('dispatch')
            ->with($event);

        $eventDispatcher->dispatch($event);
    }

    public function testDispatchWithouHandler()
    {
        $eventBus = $this->createMock(MessageBusInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $eventDispatcher = new EventDispatcher($eventBus, $logger);

        $event = new StubEvent(42);

        $eventBus->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willThrowException(new NoHandlerForMessageException('foo'));

        $logger->expects($this->once())
            ->method('notice')
            ->with('No handler for "Botilka\Tests\Domain\StubEvent".');

        $eventDispatcher->dispatch($event);
    }
}
