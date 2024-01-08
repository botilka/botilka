<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Application\EventStore;

use Botilka\Infrastructure\StoreInitializer;

final readonly class DummyEventStore implements StoreInitializer
{
    public function __construct(private bool $raiseException = false) {}

    public function initialize(bool $force = false): void
    {
        if ($this->raiseException) {
            throw new \RuntimeException('Cant\'t touch this.');
        }
    }

    public function getType(): string
    {
        return StoreInitializer::TYPE_EVENT_STORE;
    }
}
