<?php

namespace Botilka\Tests\Infrastructure\Symfony\Messenger;

use Botilka\Infrastructure\Symfony\Messenger\MessengerCommandBus;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerCommandBusTest extends TestCase
{
    public function testDispatch()
    {
        $command = new SimpleCommand('foo', 132);
        $symfonyBus = $this->createMock(MessageBusInterface::class);
        $symfonyBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willReturn('bar');

        $bus = new MessengerCommandBus($symfonyBus);
        $this->assertSame('bar', $bus->dispatch($command));
    }
}
