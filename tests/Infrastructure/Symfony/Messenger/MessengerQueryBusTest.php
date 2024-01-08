<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Symfony\Messenger;

use Botilka\Infrastructure\Symfony\Messenger\MessengerQueryBus;
use Botilka\Tests\Fixtures\Application\Query\SimpleQuery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * @internal
 */
#[CoversClass(MessengerQueryBus::class)]
final class MessengerQueryBusTest extends TestCase
{
    public function testDispatch(): void
    {
        $query = new SimpleQuery('bar', 321);
        $message = new \stdClass();
        $stamp = new HandledStamp($message, 'foo');

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::once())
            ->method('dispatch')
            ->with($query)
            ->willReturn(new Envelope($message, [$stamp]))
        ;

        $bus = new MessengerQueryBus($messageBus);
        self::assertSame($message, $bus->dispatch($query));
    }
}
