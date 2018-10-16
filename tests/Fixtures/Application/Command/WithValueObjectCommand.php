<?php

namespace Botilka\Tests\Fixtures\Application\Command;

use Botilka\Application\Command\Command;
use Botilka\Tests\Fixtures\Domain\SimpleValueObject;

final class WithValueObjectCommand implements Command
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
