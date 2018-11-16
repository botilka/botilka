<?php

declare(strict_types=1);

namespace Botilka\EventStore;

use Botilka\Event\Event;

/**
 * Represents an domain event record in the event store.
 * By creating this class, we're able to do a 'meta' management of events, we can keep the domain Event
 * as its bare minimum and use managed events in event store manager, replayer, projector, ...
 *
 * @internal
 */
final class ManagedEvent
{
    private $id;
    private $domainEvent;
    private $playhead;
    private $metadata;
    private $recordedOn;

    public function __construct(string $id, Event $domainEvent, int $playhead, ?array $metadata, \DateTimeImmutable $recordedOn)
    {
        $this->id = $id;
        $this->domainEvent = $domainEvent;
        $this->playhead = $playhead;
        $this->metadata = $metadata;
        $this->recordedOn = $recordedOn;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDomainEvent(): Event
    {
        return $this->domainEvent;
    }

    public function getPlayhead(): int
    {
        return $this->playhead;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getRecordedOn(): \DateTimeImmutable
    {
        return $this->recordedOn;
    }
}
