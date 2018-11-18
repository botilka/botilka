<?php

declare(strict_types=1);

namespace Botilka\Application\Command;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Event\Event;

final class CommandResponse
{
    private $id;
    private $event;
    private $playhead;
    private $domain;

    public function __construct(string $id, int $playhead, Event $event, string $domain)
    {
        $this->id = $id;
        $this->playhead = $playhead;
        $this->event = $event;
        $this->domain = $domain;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPlayhead(): int
    {
        return $this->playhead;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public static function withValue(EventSourcedAggregateRoot $aggregateRoot, Event $event): self
    {
        return new self($aggregateRoot->getAggregateRootId(), $aggregateRoot->getPlayhead(), $event, \get_class($aggregateRoot));
    }
}
