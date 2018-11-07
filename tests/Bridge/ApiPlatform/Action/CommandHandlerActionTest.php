<?php

namespace Botilka\Tests\Bridge\ApiPlatform\Action;

use Botilka\Application\Command\CommandBus;
use Botilka\Application\Command\CommandResponse;
use Botilka\Event\Event;
use Botilka\Bridge\ApiPlatform\Action\CommandHandlerAction;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use PHPUnit\Framework\TestCase;

class CommandHandlerActionTest extends TestCase
{
    public function testInvoke()
    {
        $command = new SimpleCommand('foo', null);
        $commandResponse = new CommandResponse('bar', 123, $this->createMock(Event::class));

        $commandBus = $this->createMock(CommandBus::class);
        $commandBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willReturn($commandResponse);
        $handler = new CommandHandlerAction($commandBus);

        $result = $handler($command);

        $this->assertSame('bar', $result->getId());
    }
}
