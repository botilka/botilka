<?php

namespace Botilka\Tests\Bridge\ApiPlatform\Action;

use Botilka\Application\Command\CommandBus;
use Botilka\Bridge\ApiPlatform\Action\CommandAction;
use Botilka\Bridge\ApiPlatform\Command\CommandResponseAdapter;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Application\Command\CommandResponse;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

final class CommandActionTest extends TestCase
{
    public function testInvoke()
    {
        $commandBus = $this->createMock(CommandBus::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $descriptionContainer = new DescriptionContainer(['foo_command' => [
            'class' => 'Foo\\BarCommand',
            'payload' => ['some' => 'string'],
        ]]);

        $commandResource = new Command('foo_command', ['foo' => 'baz']);
        $command = new SimpleCommand('foo', 3210);

        $serializer->expects($this->once())
            ->method('deserialize')
            ->with(\json_encode($commandResource->getPayload()), 'Foo\\BarCommand', 'json')
            ->willReturn($command);

        $commandResponse = new CommandResponse('foo_response_id', 123, new StubEvent(123));

        $commandBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willReturn($commandResponse);

        $action = new CommandAction($commandBus, $serializer, $descriptionContainer);

        $response = $action($commandResource);

        $this->assertInstanceOf(CommandResponseAdapter::class, $response);
        $this->assertSame('foo_response_id', $response->getId());
    }
}
