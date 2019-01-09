<?php

declare(strict_types=1);

namespace Botilka\EventStore;

use Botilka\Event\Event;

interface EventStore
{
    /**
     * @return Event[]*
     *
     * @throws AggregateRootNotFoundException
     */
    public function load(string $id): array;

    /**
     * @param int $fromPlayhead Playhead value is included
     *
     * @return Event[]
     *
     * @throws AggregateRootNotFoundException
     */
    public function loadFromPlayhead(string $id, int $fromPlayhead): array;

    /**
     * @param int $fromPlayhead Playhead value is included
     * @param int $toPlayhead   Playhead value is included
     *
     * @return Event[]
     *
     * @throws AggregateRootNotFoundException
     */
    public function loadFromPlayheadToPlayhead(string $id, int $fromPlayhead, int $toPlayhead): array;

    /**
     * @throws EventStoreConcurrencyException
     */
    public function append(string $id, int $playhead, string $type, Event $payload, ?array $metadata, \DateTimeImmutable $recordedOn, string $domain): void;
}
