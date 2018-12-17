<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\Action;

use Botilka\Application\Command\CommandBus;
use Botilka\Application\Command\CommandResponse;
use Botilka\Bridge\ApiPlatform\Action\CommandHandlerAction;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CommandHandlerActionTest extends TestCase
{
    /** @var CommandHandlerAction */
    private $handler;
    /** @var CommandBus|MockObject */
    private $commandBus;
    /** @var ValidatorInterface|MockObject */
    private $validator;

    protected function setUp()
    {
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    public function testInvoke(): void
    {
        $command = new SimpleCommand('foo');
        $commandResponse = new CommandResponse('bar', new StubEvent(123));

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willReturn($commandResponse);

        $this->validator->expects($this->once())
            ->method('validate')->with($command)
            ->willReturn([]);

        $handler = new CommandHandlerAction($this->commandBus, $this->validator);
        $result = $handler($command);

        $this->assertSame('bar', $result->getId());
    }

    /** @expectedException \ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException */
    public function testInvokeViolation(): void
    {
        $command = new SimpleCommand('foo');

        $violationList = new ConstraintViolationList([$this->createMock(ConstraintViolationInterface::class)]);

        $this->commandBus->expects($this->never())
            ->method('dispatch');

        $this->validator->expects($this->once())
            ->method('validate')->with($command)
            ->willReturn($violationList);

        $handler = new CommandHandlerAction($this->commandBus, $this->validator);
        $result = $handler($command);
    }
}
