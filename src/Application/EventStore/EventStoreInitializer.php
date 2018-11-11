<?php

namespace Botilka\Application\EventStore;

interface EventStoreInitializer
{
    /**
     * @throws \RuntimeException if store already exists
     */
    public function initialize(bool $force = false): void;
}
