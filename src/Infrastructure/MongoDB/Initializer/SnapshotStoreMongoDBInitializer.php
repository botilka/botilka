<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\MongoDB\Initializer;

use Botilka\Infrastructure\StoreInitializer;

final class SnapshotStoreMongoDBInitializer extends AbstractMongoDBStoreInitializer implements StoreInitializer
{
    public function initialize(bool $force = false): void
    {
        $this->doInitialize(['id' => 1], $force);
    }

    public function getType(): string
    {
        return StoreInitializer::TYPE_SNAPSHOT_STORE;
    }
}
