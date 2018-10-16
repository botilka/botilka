<?php

namespace Botilka\Tests\Infrastructure;

use Botilka\Command\CommandResponse;
use Botilka\Event\EventDispatcherInterface;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Botilka\Infrastructure\EventDispatcherBusMiddleware;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class EventDispatcherBusMiddlewareTest extends TestCase
{
    /** @var EventStore|MockObject */
    private $eventStore;
    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcher;
    /** @var LoggerInterface|MockObject */
    private $logger;

    public function setUp()
    {
        $this->eventStore = $this->createMock(EventStore::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testHandle()
    {
        $event = new StubEvent(1337);

        $this->eventStore->expects($this->once())
            ->method('append')
            ->with('foo', 42, StubEvent::class, $event, null, $this->isInstanceOf(\DateTimeImmutable::class));

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($event);

        $this->logger->expects($this->never())
            ->method('error');

        $commandResponse = new CommandResponse('foo', 42, $event);

        $callable = function ($message) use ($commandResponse) {
            return $commandResponse;
        };

        $middleware = new EventDispatcherBusMiddleware($this->eventStore, $this->eventDispatcher, $this->logger);

        $result = $middleware->handle('foofoo', $callable);
        $this->assertSame($commandResponse, $result);
    }

    public function testHandleEventStoreConcurrencyException()
    {
        $event = new StubEvent(1337);

        $this->eventStore->expects($this->once())
            ->method('append')
            ->willThrowException(new EventStoreConcurrencyException('bar'));

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->logger->expects($this->once())
            ->method('error')
            ->with('bar');

        $commandResponse = new CommandResponse('foo', 42, $event);

        $callable = function ($message) use ($commandResponse) {
            return $commandResponse;
        };

        $middleware = new EventDispatcherBusMiddleware($this->eventStore, $this->eventDispatcher, $this->logger);

        $this->assertNull($middleware->handle('foofoo', $callable));
    }

    public function testHandleNotCommandResponse()
    {
        $result = new \stdClass();
        $callable = function ($message) use ($result) {
            return $result;
        };

        $this->eventStore->expects($this->never())
            ->method('append');

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $middleware = new EventDispatcherBusMiddleware($this->eventStore, $this->eventDispatcher, $this->logger);
        $this->assertSame($result, $middleware->handle('foofoo', $callable));
    }
}
