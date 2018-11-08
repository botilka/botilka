<?php

namespace Botilka\Tests\Bridge\ApiPlatform\Hydrator;

use Botilka\Bridge\ApiPlatform\Hydrator\QueryHydrator;
use Botilka\Tests\Fixtures\Application\Query\SimpleQuery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class QueryHydratorTest extends TestCase
{
    /** @var QueryHydrator */
    private $hydrator;
    /** @var ValidatorInterface|MockObject */
    private $validator;
    /** @var DenormalizerInterface|MockObject */
    private $denormalizer;

    public function setUp()
    {
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->hydrator = new QueryHydrator($this->denormalizer, $this->validator);
    }

    public function testHydrate()
    {
        $Query = new SimpleQuery('foo', null);

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(['bar' => 'baz'], 'Foo\\Bar')
            ->willReturn($Query);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($Query)
            ->willReturn([]);

        $this->assertSame($Query, $this->hydrator->hydrate(['bar' => 'baz'], 'Foo\\Bar'));
    }

    /** @expectedException \ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException */
    public function testHydrateViolation()
    {
        $query = new SimpleQuery('foo', null);

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(['bar' => 'baz'], 'Foo\\Bar')
            ->willReturn($query);

        $violationList = new ConstraintViolationList([$this->createMock(ConstraintViolationInterface::class)]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($query)
            ->willReturn($violationList);

        $this->hydrator->hydrate(['bar' => 'baz'], 'Foo\\Bar');
    }
}
