<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\Query;

use Botilka\Application\Query\Query;

final class ParameterNotTypedQuery implements Query
{
    private $foo;
    /** @var int */
    private $bar;

    public function __construct(string $foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
