<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\Query;

use Botilka\Application\Query\Query;
use Botilka\Tests\Fixtures\Domain\SimpleValueObject;

final class ComplexQuery implements Query
{
    private $foo;
    private $bar;
    private $biz;
    private $lup;
    private $ool;

    public function __construct(string $foo, ?int $bar, SimpleValueObject $biz, \DateTimeImmutable $lup, ?\DateInterval $ool)
    {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->biz = $biz;
        $this->lup = $lup;
        $this->ool = $ool;
    }

    public function getFoo(): string
    {
        return $this->foo;
    }

    public function getBar(): ?int
    {
        return $this->bar;
    }

    public function getBiz(): SimpleValueObject
    {
        return $this->biz;
    }

    public function getLup(): \DateTimeImmutable
    {
        return $this->lup;
    }

    public function getOol(): ?\DateInterval
    {
        return $this->ool;
    }
}
