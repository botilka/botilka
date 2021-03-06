<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\Hydrator;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
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

    protected function setUp(): void
    {
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->hydrator = new CommandHydrator($this->denormalizer, $this->validator);
    }

    public function testHydrate(): void
    {
        $command = new SimpleCommand('foo');

        $this->denormalizer->expects(self::once())
            ->method('denormalize')
            ->with(['bar' => 'baz'], 'Foo\\Bar')
            ->willReturn($command)
        ;

        $this->validator->expects(self::once())
            ->method('validate')
            ->with($command)
            ->willReturn([])
        ;

        self::assertSame($command, $this->hydrator->hydrate(['bar' => 'baz'], 'Foo\\Bar'));
    }

    public function testHydrateViolation(): void
    {
        $command = new SimpleCommand('foo');

        $this->denormalizer->expects(self::once())
            ->method('denormalize')
            ->with(['bar' => 'baz'], 'Foo\\Bar')
            ->willReturn($command)
        ;

        $violationList = new ConstraintViolationList([$this->createMock(ConstraintViolationInterface::class)]);

        $this->validator->expects(self::once())
            ->method('validate')
            ->with($command)
            ->willReturn($violationList)
        ;

        $this->expectException(ValidationException::class);

        $this->hydrator->hydrate(['bar' => 'baz'], 'Foo\\Bar');
    }
}
