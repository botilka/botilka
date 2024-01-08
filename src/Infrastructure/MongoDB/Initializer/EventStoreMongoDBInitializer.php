<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\MongoDB\Initializer;

use Botilka\Infrastructure\StoreInitializer;

final readonly class EventStoreMongoDBInitializer extends AbstractMongoDBStoreInitializer implements StoreInitializer
{
    public function initialize(bool $force = false): void
    {
        $this->doInitialize(['id' => 1, 'playhead' => 1], $force);
    }

    public function getType(): string
    {
        return StoreInitializer::TYPE_EVENT_STORE;
    }
}
