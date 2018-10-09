<?php

namespace Botilka\Infrastructure\InMemory;

use Botilka\Domain\AggregateRoot;
use Botilka\Repository\Repository;

final class RepositoryInMemory implements Repository
{
    private $data = [];

    public function get(string $id): ?AggregateRoot
    {
        return $this->data[$id] ?? null;
    }

    public function add(AggregateRoot $aggregate)
    {
        $this->data[$aggregate->getAggregateRootId()] = $aggregate;
    }

    public function save(AggregateRoot $aggregate)
    {
        $this->data[$aggregate->getAggregateRootId()] = $aggregate;
    }

    public function delete(string $id): void
    {
        unset($this->data[$id]);
    }

    public function all(): array
    {
        return $this->data;
    }
}
