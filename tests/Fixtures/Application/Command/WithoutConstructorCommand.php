<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\Command;

use Botilka\Application\Command\Command;

final class WithoutConstructorCommand implements Command
{
    private ?string $foo = null;
    private ?int $bar = null;

    public function setFoo(string $foo): self
    {
        $this->foo = $foo;

        return $this;
    }

    public function getFoo(): Nstring
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
