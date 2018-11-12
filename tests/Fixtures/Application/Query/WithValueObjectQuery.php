<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\Query;

use Botilka\Application\Query\Query;
use Botilka\Tests\Fixtures\Domain\SimpleValueObject;

final class WithValueObjectQuery implements Query
{
    private $foo;
    private $bar;
    private $biz;

    public function __construct(string $foo, ?int $bar, SimpleValueObject $biz)
    {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->biz = $biz;
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
}
