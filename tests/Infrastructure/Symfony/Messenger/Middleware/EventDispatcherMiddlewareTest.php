<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Symfony\Messenger\Middleware;

use Botilka\Application\Command\CommandResponse;
use Botilka\Event\EventBus;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Botilka\Infrastructure\Symfony\Messenger\Middleware\EventDispatcherMiddleware;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;

final class EventDispatcherMiddlewareTest extends TestCase
{
    /** @var EventStore|MockObject */
    private $eventStore;
    /** @var EventBus|MockObject */
    private $eventBus;
    /** @var LoggerInterface|MockObject */
    private $logger;

    public function setUp()
    {
        $this->eventStore = $this->createMock(EventStore::class);
        $this->eventBus = $this->createMock(EventBus::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testHandle(): void
    {
        $event = new StubEvent(1337);

        $this->eventStore->expects($this->once())
            ->method('append')
            ->with('foo', 42, StubEvent::class, $event, null, $this->isInstanceOf(\DateTimeImmutable::class));

        $this->eventBus->expects($this->once())
            ->method('dispatch')
            ->with($event);

        $this->logger->expects($this->never())
            ->method('error');

        $commandResponse = new CommandResponse('foo', 42, $event);

        $callable = function ($message) use ($commandResponse) {
            return $commandResponse;
        };

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->eventBus, $this->logger);

        $result = $middleware->handle('foofoo', $callable);
        $this->assertSame($commandResponse, $result);
    }

    public function testHandleNoHandlerForMessageException(): void
    {
        $event = new StubEvent(1337);

        $this->eventStore->expects($this->once())
            ->method('append')
            ->with('foo', 42, StubEvent::class, $event, null, $this->isInstanceOf(\DateTimeImmutable::class));

        $this->eventBus->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willThrowException(new NoHandlerForMessageException());

        $this->logger->expects($this->once())
            ->method('notice')
            ->with(\sprintf('No handler for "%s".', \get_class($event)));

        $commandResponse = new CommandResponse('foo', 42, $event);

        $callable = function ($message) use ($commandResponse) {
            return $commandResponse;
        };

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->eventBus, $this->logger);

        $result = $middleware->handle('foofoo', $callable);
        $this->assertSame($commandResponse, $result);
    }

    public function testHandleEventStoreConcurrencyException(): void
    {
        $event = new StubEvent(1337);

        $this->eventStore->expects($this->once())
            ->method('append')
            ->willThrowException(new EventStoreConcurrencyException('bar'));

        $this->eventBus->expects($this->never())
            ->method('dispatch');

        $this->logger->expects($this->once())
            ->method('error')
            ->with('bar');

        $commandResponse = new CommandResponse('foo', 42, $event);

        $callable = function ($message) use ($commandResponse) {
            return $commandResponse;
        };

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->eventBus, $this->logger);

        $this->assertNull($middleware->handle('foofoo', $callable));
    }

    public function testHandleNotCommandResponse(): void
    {
        $result = new \stdClass();
        $callable = function ($message) use ($result) {
            return $result;
        };

        $this->eventStore->expects($this->never())
            ->method('append');

        $this->eventBus->expects($this->never())
            ->method('dispatch');

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->eventBus, $this->logger);
        $this->assertSame($result, $middleware->handle('foofoo', $callable));
    }
}
