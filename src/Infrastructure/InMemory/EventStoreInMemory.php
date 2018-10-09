<?php

namespace Botilka\Infrastructure\InMemory;

use Botilka\Event\Event;
use Botilka\EventStore\EventStore;

final class EventStoreInMemory implements EventStore
{
    private $store = [];

    public function load(string $id): array
    {
        return $this->store[$id];
    }

    public function loadFromPlayhead(string $id, int $fromPlayhead): array
    {
        return \array_slice($this->store[$id], $fromPlayhead, null, true);
    }

    public function loadFromPlayheadToPlayhead(string $id, int $fromPlayhead, int $toPlayhead): array
    {
        return \array_slice($this->loadFromPlayhead($id, $fromPlayhead), 0, $toPlayhead - $fromPlayhead + 1, true);
    }

    /**
     * We can't have a concurrency here as memory is not shared.
     */
    public function append(string $id, int $playhead, string $type, Event $payload, ?array $metadata, \DateTimeImmutable $recordedOn)
    {
        $this->store[$id][$playhead] = $payload;
    }
}
