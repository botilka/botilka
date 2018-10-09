<?php

namespace Botilka\Repository;

use Botilka\Domain\AggregateRoot;

interface Repository
{
    public function get(string $id): ?AggregateRoot;

    public function add(AggregateRoot $aggregate);

    public function save(AggregateRoot $aggregate);

    public function delete(string $id): void;

    public function all(): array;
}
