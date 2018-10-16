<?php

namespace Botilka\Tests\Infrastructure\Symfony\Messenger;

use Botilka\Infrastructure\Symfony\Messenger\SymfonyEventBus;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class SymfonyEventBusTest extends TestCase
{
    public function testDispatch()
    {
        $event = new StubEvent(42);

        $symfonyBus = $this->createMock(MessageBusInterface::class);
        $symfonyBus->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willReturn('bar');

        $bus = new SymfonyEventBus($symfonyBus);
        $this->assertSame('bar', $bus->dispatch($event));
    }
}
