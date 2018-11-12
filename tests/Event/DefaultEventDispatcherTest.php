<?php

declare(strict_types=1);

namespace Botilka\Tests\Event;

use Botilka\Event\DefaultEventDispatcher;
use Botilka\Event\EventBus;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;

final class DefaultEventDispatcherTest extends TestCase
{
    public function testDispatchWithHandler(): void
    {
        $eventBus = $this->createMock(EventBus::class);
        $logger = $this->createMock(LoggerInterface::class);
        $eventDispatcher = new DefaultEventDispatcher($eventBus, $logger);

        $event = new StubEvent(42);

        $eventBus->expects($this->once())
            ->method('dispatch')
            ->with($event);

        $eventDispatcher->dispatch($event);
    }

    public function testDispatchWithouHandler(): void
    {
        $eventBus = $this->createMock(EventBus::class);
        $logger = $this->createMock(LoggerInterface::class);
        $eventDispatcher = new DefaultEventDispatcher($eventBus, $logger);

        $event = new StubEvent(42);

        $eventBus->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willThrowException(new NoHandlerForMessageException('foo'));

        $logger->expects($this->once())
            ->method('notice')
            ->with('No handler for "Botilka\Tests\Fixtures\Domain\StubEvent".');

        $eventDispatcher->dispatch($event);
    }
}
