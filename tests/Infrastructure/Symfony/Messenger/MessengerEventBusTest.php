<?php

namespace Botilka\Tests\Infrastructure\Symfony\Messenger;

use Botilka\Infrastructure\Symfony\Messenger\MessengerEventBus;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerEventBusTest extends TestCase
{
    public function testDispatch(): void
    {
        $event = new StubEvent(42);

        $messengerBus = $this->createMock(MessageBusInterface::class);
        $messengerBus->expects($this->once())
            ->method('dispatch')
            ->with($event);

        $bus = new MessengerEventBus($messengerBus);
        $bus->dispatch($event);
    }
}
