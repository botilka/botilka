<?php

declare(strict_types=1);

namespace Botilka\Application\Command;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Event\Event;

/**
 * When a command handler use an event sourced aggregate root, it must return
 * some more information the aggregate id & the event.
 */
final class EventSourcedCommandResponse extends CommandResponse
{
    private $playhead;
    private $domain;

    public function __construct(string $id, Event $event, int $playhead, string $domain)
    {
        parent::__construct($id, $event);
        $this->playhead = $playhead;
        $this->domain = $domain;
    }

    public function getPlayhead(): int
    {
        return $this->playhead;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public static function fromEventSourcedAggregateRoot(EventSourcedAggregateRoot $aggregateRoot, Event $event, ?string $domain = null): self
    {
        return new self($aggregateRoot->getAggregateRootId(), $event, $aggregateRoot->getPlayhead(), $domain ?? \get_class($aggregateRoot));
    }
}
