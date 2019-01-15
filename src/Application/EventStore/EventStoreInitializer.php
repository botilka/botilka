<?php

declare(strict_types=1);

namespace Botilka\Application\EventStore;

interface EventStoreInitializer
{
    /**
     * @throws \RuntimeException if the store already exists
     */
    public function initialize(bool $force = false): void;
}
