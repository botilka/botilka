<?php

declare(strict_types=1);

namespace Botilka\Infrastructure;

interface StoreInitializer
{
    public const TYPE_EVENT_STORE = 'event';
    public const TYPE_SNAPSHOT_STORE = 'snapshot';

    public const TYPES = [
        self::TYPE_EVENT_STORE,
        self::TYPE_SNAPSHOT_STORE,
    ];

    /**
     * @throws \RuntimeException if the store already exists
     */
    public function initialize(bool $force = false): void;

    public function getType(): string;
}
