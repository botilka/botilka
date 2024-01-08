<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\Query;

use Botilka\Application\Query\Query;

final class WithoutConstructorQuery implements Query
{
    private ?string $foo = null;
    private ?int $bar = null;

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
