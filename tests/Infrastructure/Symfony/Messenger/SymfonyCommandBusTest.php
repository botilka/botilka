<?php

namespace Botilka\Tests\Infrastructure\Symfony\Messenger;

use Botilka\Infrastructure\Symfony\Messenger\SymfonyCommandBus;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class SymfonyCommandBusTest extends TestCase
{
    public function testDispatch()
    {
        $command = new SimpleCommand('foo', 132);
        $symfonyBus = $this->createMock(MessageBusInterface::class);
        $symfonyBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willReturn('bar');

        $bus = new SymfonyCommandBus($symfonyBus);
        $this->assertSame('bar', $bus->dispatch($command));
    }
}
