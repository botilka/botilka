<?php

declare(strict_types=1);

namespace Botilka\EventStore;

use Botilka\Event\Event;

/**
 * Represents an domain event record in the event store.
 * By creating this class, we're able to do a 'meta' management of events, we can keep the domain Event
 * to its bare minimum and use managed events in event store manager, replayer, projector, ...
 *
 * @internal
 */
final readonly class ManagedEvent
{
    /**
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        private string $id,
        private Event $domainEvent,
        private int $playhead,
        private ?array $metadata,
        private \DateTimeImmutable $recordedOn,
        private string $domain,
    ) {}

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

    /**
     * @return array<string, mixed>|null
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getRecordedOn(): \DateTimeImmutable
    {
        return $this->recordedOn;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }
}
