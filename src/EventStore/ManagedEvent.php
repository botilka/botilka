<?php

declare(strict_types=1);

namespace Botilka\EventStore;

use Botilka\Event\Event;

/**
 * Represents an domain event record in the event store.
 * By creating this class, we're able to do a 'meta' management of events, we can keep the domain Event
 * as its bare minimum and use managed events in event store manager, replayer, projector, ...
 */
interface ManagedEvent
{
    public function getDomainEvent(): Event;

    public function getPlayhead(): int;

    public function getMetadata(): ?array;

    public function getRecordedOn(): \DateTimeImmutable;
}
