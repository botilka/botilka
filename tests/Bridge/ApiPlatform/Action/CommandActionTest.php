<?php

namespace Botilka\Tests\Bridge\ApiPlatform\Action;

use Botilka\Bridge\ApiPlatform\Action\CommandAction;
use Botilka\Bridge\ApiPlatform\Command\CommandResponseAdapter;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Command\CommandResponse;
use Botilka\Tests\Domain\StubEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class CommandActionTest extends TestCase
{
    public function testInvoke()
    {
        $commandBus = $this->createMock(MessageBusInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $descriptionContainer = new DescriptionContainer(['foo_command' => [
            'class' => 'Foo\\BarCommand',
            'payload' => ['some' => 'string'],
        ]]);

        $commandResource = new Command('foo_command', ['foo' => 'baz']);

        $serializer->expects($this->once())
            ->method('deserialize')
            ->with(\json_encode($commandResource->getPayload()), 'Foo\\BarCommand', 'json')
            ->willReturn('foo');

        $commandResponse = new CommandResponse('foo_response_id', 123, new StubEvent(123));

        $commandBus->expects($this->once())
            ->method('dispatch')
            ->with('foo')
            ->willReturn($commandResponse);

        $action = new CommandAction($commandBus, $serializer, $descriptionContainer);

        $response = $action($commandResource);

        $this->assertInstanceOf(CommandResponseAdapter::class, $response);
        $this->assertSame('foo_response_id', $response->getId());
    }
}
