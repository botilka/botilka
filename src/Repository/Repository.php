<?php

declare(strict_types=1);

namespace Botilka\Repository;

use Botilka\Domain\AggregateRoot;

interface Repository
{
    public function get(string $id): ?AggregateRoot;

    public function add(AggregateRoot $aggregate): void;

    public function save(AggregateRoot $aggregate): void;

    public function delete(string $id): void;

    public function all(): array;
}
