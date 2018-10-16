<?php

namespace Botilka\Tests\Infrastructure;

use Botilka\Command\CommandResponse;
use Botilka\Event\EventDispatcherInterface;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Botilka\Infrastructure\EventDispatcherBusMiddleware;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class EventDispatcherBusMiddlewareTest extends TestCase
{
    public function testHandle()
    {
        $eventStore = $this->createMock(EventStore::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $event = new StubEvent(1337);

        $eventStore->expects($this->once())
            ->method('append')
            ->with('foo', 42, StubEvent::class, $event, null, $this->isInstanceOf(\DateTimeImmutable::class));

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($event);

        $logger->expects($this->never())
            ->method('error');

        $commandResponse = new CommandResponse('foo', 42, $event);

        $callable = function ($message) use ($commandResponse) {
            return $commandResponse;
        };

        $middleware = new EventDispatcherBusMiddleware($eventStore, $eventDispatcher, $logger);

        $result = $middleware->handle('foofoo', $callable);
        $this->assertSame($commandResponse, $result);
    }

    public function testHandleEventStoreConcurrencyException()
    {
        $eventStore = $this->createMock(EventStore::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $event = new StubEvent(1337);

        $eventStore->expects($this->once())
            ->method('append')
            ->willThrowException(new EventStoreConcurrencyException('bar'));

        $eventDispatcher->expects($this->never())
            ->method('dispatch');

        $logger->expects($this->once())
            ->method('error')
            ->with('bar');

        $commandResponse = new CommandResponse('foo', 42, $event);

        $callable = function ($message) use ($commandResponse) {
            return $commandResponse;
        };

        $middleware = new EventDispatcherBusMiddleware($eventStore, $eventDispatcher, $logger);

        $this->assertNull($middleware->handle('foofoo', $callable));
    }
}
