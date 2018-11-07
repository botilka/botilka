<?php

namespace Botilka\Tests\Bridge\ApiPlatform\Hydrator;

use Botilka\Bridge\ApiPlatform\Hydrator\HydrationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class HydrationExceptionTest extends TestCase
{
    public function testGetConstraintViolationList()
    {
        $violationsList = $this->createMock(ConstraintViolationListInterface::class);
        $exception = new HydrationException($violationsList);
        $this->assertSame($violationsList, $exception->getConstraintViolationList());
    }
}
