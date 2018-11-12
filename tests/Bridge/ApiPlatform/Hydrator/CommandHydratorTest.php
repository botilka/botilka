<?php

namespace Botilka\Tests\Bridge\ApiPlatform\Hydrator;

use Botilka\Bridge\ApiPlatform\Hydrator\CommandHydrator;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CommandHydratorTest extends TestCase
{
    /** @var CommandHydrator */
    private $hydrator;
    /** @var ValidatorInterface|MockObject */
    private $validator;
    /** @var DenormalizerInterface|MockObject */
    private $denormalizer;

    public function setUp()
    {
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->hydrator = new CommandHydrator($this->denormalizer, $this->validator);
    }

    public function testHydrate(): void
    {
        $command = new SimpleCommand('foo', null);

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(['bar' => 'baz'], 'Foo\\Bar')
            ->willReturn($command);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($command)
            ->willReturn([]);

        $this->assertSame($command, $this->hydrator->hydrate(['bar' => 'baz'], 'Foo\\Bar'));
    }

    /** @expectedException \ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException */
    public function testHydrateViolation(): void
    {
        $command = new SimpleCommand('foo', null);

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(['bar' => 'baz'], 'Foo\\Bar')
            ->willReturn($command);

        $violationList = new ConstraintViolationList([$this->createMock(ConstraintViolationInterface::class)]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($command)
            ->willReturn($violationList);

        $this->hydrator->hydrate(['bar' => 'baz'], 'Foo\\Bar');
    }
}
