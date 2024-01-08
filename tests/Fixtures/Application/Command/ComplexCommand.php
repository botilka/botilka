<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\Command;

use Botilka\Application\Command\Command;
use Botilka\Tests\Fixtures\Domain\SimpleValueObject;

final readonly class ComplexCommand implements Command
{
    public function __construct(private string $foo, private ?int $bar, private SimpleValueObject $biz, private \DateTimeImmutable $lup, private ?\DateInterval $ool) {}

    public function getFoo(): string
    {
        return $this->foo;
    }

    public function getBar(): ?int
    {
        return $this->bar;
    }

    public function getBiz(): SimpleValueObject
    {
        return $this->biz;
    }

    public function getLup(): \DateTimeImmutable
    {
        return $this->lup;
    }

    public function getOol(): ?\DateInterval
    {
        return $this->ool;
    }
}
