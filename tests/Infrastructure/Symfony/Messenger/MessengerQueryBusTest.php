<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Symfony\Messenger;

use Botilka\Infrastructure\Symfony\Messenger\MessengerQueryBus;
use Botilka\Tests\Fixtures\Application\Query\SimpleQuery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class MessengerQueryBusTest extends TestCase
{
    public function testDispatch(): void
    {
        $query = new SimpleQuery('bar', 321);
        $message = new \stdClass();
        $stamp = new HandledStamp($message, 'foo');

        $messengerBus = $this->createMock(MessageBusInterface::class);
        $messengerBus->expects($this->once())
            ->method('dispatch')
            ->with($query)
            ->willReturn(new Envelope($message, $stamp));

        $bus = new MessengerQueryBus($messengerBus);
        $this->assertSame($message, $bus->dispatch($query));
    }
}
