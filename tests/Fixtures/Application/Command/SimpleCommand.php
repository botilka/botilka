<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\Command;

use Botilka\Application\Command\Command;

final readonly class SimpleCommand implements Command
{
    public function __construct(private string $foo, private ?int $bar = null) {}

    public function getFoo(): string
    {
        return $this->foo;
    }

    public function getBar(): ?int
    {
        return $this->bar;
    }
}
