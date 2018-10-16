<?php

namespace Botilka\Tests\Fixtures\Domain;

use Botilka\Domain\AggregateRoot;

final class StubAggregateRoot implements AggregateRoot
{
    private $rootId;

    public function __construct(string $rootId)
    {
        $this->rootId = $rootId;
    }

    public function getAggregateRootId(): string
    {
        return $this->rootId;
    }
}
