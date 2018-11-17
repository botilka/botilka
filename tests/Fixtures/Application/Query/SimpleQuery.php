<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\Query;

use Botilka\Application\Query\Query;

final class SimpleQuery implements Query
{
    private $foo;
    private $bar;

    public function __construct(string $foo, ?int $bar = null)
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
