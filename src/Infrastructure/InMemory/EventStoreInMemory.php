<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\InMemory;

use Botilka\Event\Event;
use Botilka\EventStore\EventStore;

final class EventStoreInMemory implements EventStore
{
    private $store = [];

    public function load(string $id): array
    {
        return \array_map(function ($event) {
            return $event['payload'];
        }, $this->store[$id]);
    }

    public function loadFromPlayhead(string $id, int $fromPlayhead): array
    {
        return \array_map(function ($event) {
            return $event['payload'];
        }, \array_slice($this->store[$id], $fromPlayhead, null, true));
    }

    public function loadFromPlayheadToPlayhead(string $id, int $fromPlayhead, int $toPlayhead): array
    {
        return \array_slice($this->loadFromPlayhead($id, $fromPlayhead), 0, $toPlayhead - $fromPlayhead + 1, true);
    }

    /**
     * We can't have a write concurrency here as memory is not shared AND it's a single thread.
     */
    public function append(string $id, int $playhead, string $type, Event $payload, ?array $metadata, \DateTimeImmutable $recordedOn): void
    {
        $this->store[$id][$playhead] = [
            'id' => $id,
            'playhead' => $playhead,
            'type' => $type,
            'payload' => $payload,
            'metadata' => $metadata,
            'recordedOn' => $recordedOn,
        ];
    }

    public function getStore(): array
    {
        return $this->store;
    }
}
