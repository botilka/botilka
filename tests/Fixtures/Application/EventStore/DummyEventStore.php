<?php

namespace Botilka\Tests\Fixtures\Application\EventStore;

use Botilka\Application\EventStore\EventStoreInitializer;

final class DummyEventStore implements EventStoreInitializer
{
    private $raiseException;

    public function __construct(bool $raiseException = false)
    {
        $this->raiseException = $raiseException;
    }

    public function initialize(bool $force = false): void
    {
        if (true === $this->raiseException) {
            throw new \RuntimeException('Cant\t touch this.');
        }
    }
}
