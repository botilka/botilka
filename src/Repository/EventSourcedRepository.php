<?php

declare(strict_types=1);

namespace Botilka\Repository;

use Botilka\Application\Command\EventSourcedCommandResponse;
use Botilka\Domain\EventSourcedAggregateRoot;

interface EventSourcedRepository
{
    public function load(string $id): EventSourcedAggregateRoot;

    public function save(EventSourcedCommandResponse $commandResponse): void;
}
