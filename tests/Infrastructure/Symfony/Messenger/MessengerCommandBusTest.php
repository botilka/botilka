<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Symfony\Messenger;

use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandResponse;
use Botilka\Infrastructure\Symfony\Messenger\MessengerCommandBus;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class MessengerCommandBusTest extends TestCase
{
    private function getMessengerCommandBus(Command $command, ?CommandResponse $commandResponse, StampInterface $stamp): MessengerCommandBus
    {
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willReturn(new Envelope($command, [$stamp]));

        return new MessengerCommandBus($messageBus);
    }

    public function testDispatchHandled(): void
    {
        $command = new SimpleCommand('foo', 132);
        $commandResponse = new CommandResponse('foo', new StubEvent(123));
        $stamp = new HandledStamp($commandResponse, 'foo');

        $bus = $this->getMessengerCommandBus($command, $commandResponse, $stamp);

        $result = $bus->dispatch($command);
        $this->assertSame($commandResponse, $result);
        $this->assertSame($commandResponse->getId(), $result->getId());
    }

    public function testDispatchSent(): void
    {
        $command = new SimpleCommand('foo', 132);
        $commandResponse = null;
        $stamp = new SentStamp(\get_class($this), 'this');

        $bus = $this->getMessengerCommandBus($command, null, $stamp);

        $result = $bus->dispatch($command);
        $this->assertNull($result);
    }
}
