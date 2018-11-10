<?php

namespace Botilka\Application\EventStore;

interface EventStoreUniqueIndex
{
    public function createIndex(string $projectDir = null): void;
}
