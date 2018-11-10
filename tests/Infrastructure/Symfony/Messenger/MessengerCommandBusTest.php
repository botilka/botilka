<?php

namespace Botilka\Tests\Infrastructure\Symfony\Messenger;

use Botilka\Application\Command\CommandResponse;
use Botilka\Infrastructure\Symfony\Messenger\MessengerCommandBus;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerCommandBusTest extends TestCase
{
    public function testDispatch()
    {
        $command = new SimpleCommand('foo', 132);
        $commandResponse = new CommandResponse('bar', 123, new StubEvent(123));

        $messengerBus = $this->createMock(MessageBusInterface::class);
        $messengerBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willReturn($commandResponse);

        $bus = new MessengerCommandBus($messengerBus);
        $this->assertSame($commandResponse, $bus->dispatch($command));
    }
}
