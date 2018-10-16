<?php

namespace Botilka\Tests\Fixtures\Domain;

final class SimpleValueObject
{
    public $baz;
    public $buz;

    public function __construct(string $baz, float $buz)
    {
        $this->baz = $baz;
        $this->buz = $buz;
    }
}
