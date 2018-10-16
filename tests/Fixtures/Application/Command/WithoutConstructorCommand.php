<?php

namespace Botilka\Tests\Fixtures\Application\Command;

use Botilka\Application\Command\Command;

final class WithoutConstructorCommand implements Command
{
    /** @var string */
    private $foo;
    /** @var ?int */
    private $bar;

    public function setFoo(string $foo): self
    {
        $this->foo = $foo;

        return $this;
    }

    public function getFoo(): string
    {
        return $this->foo;
    }

    public function setBar(?int $bar): self
    {
        $this->bar = $bar;

        return $this;
    }

    public function getBar(): ?int
    {
        return $this->bar;
    }
}
