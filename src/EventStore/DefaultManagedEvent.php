<?php

declare(strict_types=1);

namespace Botilka\EventStore;

use Botilka\Event\Event;

final class DefaultManagedEvent implements ManagedEvent
{
    private $domainEvent;
    private $playhead;
    private $metadata;
    private $recordedOn;

    public function __construct(Event $domainEvent, int $playhead, ?array $metadata, \DateTimeImmutable $recordedOn)
    {
        $this->domainEvent = $domainEvent;
        $this->playhead = $playhead;
        $this->metadata = $metadata;
        $this->recordedOn = $recordedOn;
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
