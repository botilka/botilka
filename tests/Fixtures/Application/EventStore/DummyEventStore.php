<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\EventStore;

use Botilka\Infrastructure\StoreInitializer;

final class DummyEventStore implements StoreInitializer
{
    private $raiseException;

    public function __construct(bool $raiseException = false)
    {
        $this->raiseException = $raiseException;
    }

    public function initialize(bool $force = false): void
    {
        if (true === $this->raiseException) {
            throw new \RuntimeException('Cant\'t touch this.');
        }
    }

    public function getType(): string
    {
        return StoreInitializer::TYPE_EVENT_STORE;
    }
}
