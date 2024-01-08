<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\Query;

use Botilka\Application\Query\Query;

final readonly class SimpleQuery implements Query
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
