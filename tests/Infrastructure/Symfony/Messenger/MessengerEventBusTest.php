<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Symfony\Messenger;

use Botilka\Infrastructure\Symfony\Messenger\MessengerEventBus;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[CoversClass(MessengerEventBus::class)]
final class MessengerEventBusTest extends TestCase
{
    public function testDispatch(): void
    {
        $event = new StubEvent(42);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::once())
            ->method('dispatch')
            ->with($event)
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $bus = new MessengerEventBus($messageBus);
        $bus->dispatch($event);
    }
}
