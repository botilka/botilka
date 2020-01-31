<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\DataPersister;

use Botilka\Application\Command\CommandBus;
use Botilka\Application\Command\CommandResponse;
use Botilka\Bridge\ApiPlatform\Command\CommandResponseAdapter;
use Botilka\Bridge\ApiPlatform\DataPersister\CommandBusPersister;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CommandBusPersisterTest extends TestCase
{
    /** @var CommandBusPersister */
    private $handler;
    /** @var CommandBus|MockObject */
    private $commandBus;
    /** @var ValidatorInterface|MockObject */
    private $validator;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    public function testPersist(): void
    {
        $command = new SimpleCommand('foo');
        $commandResponse = new CommandResponse('bar', new StubEvent(123));

        $this->commandBus->expects(self::once())
            ->method('dispatch')
            ->with($command)
            ->willReturn($commandResponse)
        ;

        $this->validator->expects(self::once())
            ->method('validate')->with($command)
            ->willReturn([])
        ;

        $persister = new CommandBusPersister($this->commandBus, $this->validator);
        $result = $persister->persist($command);

        self::assertInstanceOf(CommandResponseAdapter::class, $result);
        self::assertSame('bar', $result->getId());
    }

    /** @expectedException \ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException */
    public function testPersistViolation(): void
    {
        $command = new SimpleCommand('foo');

        $violationList = new ConstraintViolationList([$this->createMock(ConstraintViolationInterface::class)]);

        $this->commandBus->expects(self::never())
            ->method('dispatch')
        ;

        $this->validator->expects(self::once())
            ->method('validate')->with($command)
            ->willReturn($violationList)
        ;

        $persister = new CommandBusPersister($this->commandBus, $this->validator);
        $result = $persister->persist($command);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Remove must not be called in an event-sourced application.
     */
    public function testRemove()
    {
        $command = new SimpleCommand('foo');
        $persister = new CommandBusPersister($this->commandBus, $this->validator);

        $persister->remove($command);
    }

    /** @dataProvider supportsProvider */
    public function testSupports($data, bool $expected)
    {
        $persister = new CommandBusPersister($this->commandBus, $this->validator);
        self::assertSame($expected, $persister->supports($data));
    }

    public function supportsProvider(): array
    {
        return [
            [new SimpleCommand('foo'), true],
            [new \DateTime(), false],
        ];
    }
}
