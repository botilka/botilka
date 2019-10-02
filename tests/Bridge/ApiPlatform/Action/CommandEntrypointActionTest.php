<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\Action;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Botilka\Application\Command\CommandBus;
use Botilka\Application\Command\CommandResponse;
use Botilka\Bridge\ApiPlatform\Action\CommandEntrypointAction;
use Botilka\Bridge\ApiPlatform\Command\CommandResponseAdapter;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Hydrator\CommandHydratorInterface;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class CommandEntrypointActionTest extends TestCase
{
    public function testInvoke(): void
    {
        $commandBus = $this->createMock(CommandBus::class);
        $hydrator = $this->createMock(CommandHydratorInterface::class);
        $descriptionContainer = new DescriptionContainer(['foo' => [
            'class' => 'Foo\\Bar',
            'payload' => ['some' => 'string'],
        ]]);

        $commandResource = new Command('foo', ['foo' => 'baz']);
        $command = new SimpleCommand('foo', 3210);

        $hydrator->expects(self::once())
            ->method('hydrate')
            ->with($commandResource->getPayload(), 'Foo\\Bar')
            ->willReturn($command)
        ;

        $commandResponse = new CommandResponse('foo', new StubEvent(123));

        $commandBus->expects(self::once())
            ->method('dispatch')
            ->with($command)
            ->willReturn($commandResponse)
        ;

        $action = new CommandEntrypointAction($commandBus, $descriptionContainer, $hydrator);

        $response = $action($commandResource);

        self::assertInstanceOf(CommandResponseAdapter::class, $response);
        self::assertSame('foo', $response->getId());
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Command 'foo' not found.
     */
    public function testInvokeNotFound(): void
    {
        $commandBus = $this->createMock(CommandBus::class);
        $hydrator = $this->createMock(CommandHydratorInterface::class);
        $descriptionContainer = new DescriptionContainer();

        $commandResource = new Command('foo', ['foo' => 'baz']);

        $commandBus->expects(self::never())
            ->method('dispatch')
        ;

        $action = new CommandEntrypointAction($commandBus, $descriptionContainer, $hydrator);

        $response = $action($commandResource);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testInvokeBadRequest(): void
    {
        $commandBus = $this->createMock(CommandBus::class);
        $hydrator = $this->createMock(CommandHydratorInterface::class);

        $hydrator->expects(self::once())
            ->method('hydrate')
            ->with(['foo' => 'baz'], 'Foo\\Bar')
            ->willThrowException(new ValidationException($this->createMock(ConstraintViolationListInterface::class)))
        ;

        $descriptionContainer = new DescriptionContainer(['foo' => [
            'class' => 'Foo\\Bar',
            'payload' => ['some' => 'string'],
        ]]);

        $commandResource = new Command('foo', ['foo' => 'baz']);

        $commandBus->expects(self::never())
            ->method('dispatch')
        ;

        $action = new CommandEntrypointAction($commandBus, $descriptionContainer, $hydrator);

        $response = $action($commandResource);
    }
}
