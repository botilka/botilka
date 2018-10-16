<?php

namespace Botilka\Tests\Fixtures\Domain;

use Botilka\Event\Event;

final class StubEvent implements Event
{
    private $foo;

    public function __construct(int $foo)
    {
        $this->foo = $foo;
    }

    public function getFoo(): int
    {
        return $this->foo;
    }
}
