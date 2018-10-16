<?php

namespace Botilka\Tests\Infrastructure\Symfony\Messenger;

use Botilka\Infrastructure\Symfony\Messenger\SymfonyQueryBus;
use Botilka\Tests\Fixtures\Application\Query\SimpleQuery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class SymfonyQueryBusTest extends TestCase
{
    public function testDispatch()
    {
        $query = new SimpleQuery('bar', 321);

        $symfonyBus = $this->createMock(MessageBusInterface::class);
        $symfonyBus->expects($this->once())
            ->method('dispatch')
            ->with($query)
            ->willReturn('bar');

        $bus = new SymfonyQueryBus($symfonyBus);
        $this->assertSame('bar', $bus->dispatch($query));
    }
}
