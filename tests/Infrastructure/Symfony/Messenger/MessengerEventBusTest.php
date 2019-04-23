<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Symfony\Messenger;

use Botilka\Infrastructure\Symfony\Messenger\MessengerEventBus;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerEventBusTest extends TestCase
{
    public function testDispatch(): void
    {
        $event = new StubEvent(42);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willReturn(new Envelope(new \stdClass()));

        $bus = new MessengerEventBus($messageBus);
        $bus->dispatch($event);
    }
}
