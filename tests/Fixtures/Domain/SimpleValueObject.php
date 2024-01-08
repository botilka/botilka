<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Domain;

final class SimpleValueObject
{
    public function __construct(public string $baz, public float $buz) {}
}
