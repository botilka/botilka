<?php

namespace Botilka\Tests\Bridge\ApiPlatform\Action;

use Botilka\Application\Command\CommandBus;
use Botilka\Application\Command\CommandResponse;
use Botilka\Bridge\ApiPlatform\Action\CommandHandlerAction;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;

final class CommandHandlerActionTest extends TestCase
{
    public function testInvoke(): void
    {
        $command = new SimpleCommand('foo', null);
        $commandResponse = new CommandResponse('bar', 123, new StubEvent(123));

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
