<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\Command;

use Botilka\Application\Command\Command;

final class ParameterNotTypedCommand implements Command
{
    private $foo;
    private $bar;

    public function __construct(string $foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
