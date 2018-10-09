<?php

namespace Botilka\EventStore;

use Botilka\Event\Event;

interface EventStore
{
    public function load(string $id): array;

    /**
     * @param int $fromPlayhead Playhead value is included
     */
    public function loadFromPlayhead(string $id, int $fromPlayhead): array;

    /**
     * @param int $fromPlayhead Playhead value is included
     * @param int $toPlayhead   Playhead value is included
     */
    public function loadFromPlayheadToPlayhead(string $id, int $fromPlayhead, int $toPlayhead): array;

    /**
     * @throws EventStoreConcurrencyException
     */
    public function append(string $id, int $playhead, string $type, Event $payload, ?array $metadata, \DateTimeImmutable $recordedOn);
}
