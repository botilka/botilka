<?php

namespace Botilka\Tests\Fixtures\Application\Command;

use Botilka\Application\Command\Command;

final class SimpleCommand implements Command
{
    private $foo;
    private $bar;

    public function __construct(string $foo, ?int $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function getFoo(): string
    {
        return $this->foo;
    }

    public function getBar(): ?int
    {
        return $this->bar;
    }
}
