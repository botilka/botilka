<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Domain;

use Botilka\Domain\AggregateRoot;

final readonly class StubAggregateRoot implements AggregateRoot
{
    public function __construct(private string $rootId) {}

    public function getAggregateRootId(): string
    {
        return $this->rootId;
    }
}
