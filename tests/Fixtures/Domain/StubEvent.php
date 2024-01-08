<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Domain;

use Botilka\Event\Event;

final readonly class StubEvent implements Event
{
    public function __construct(private int $foo) {}

    public function getFoo(): int
    {
        return $this->foo;
    }
}
