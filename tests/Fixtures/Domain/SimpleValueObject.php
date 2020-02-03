<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Domain;

final class SimpleValueObject
{
    /** @var string */
    public $baz;

    /** @var float */
    public $buz;

    public function __construct(string $baz, float $buz)
    {
        $this->baz = $baz;
        $this->buz = $buz;
    }
}
