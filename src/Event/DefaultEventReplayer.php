<?php

namespace Botilka\Event;

use Botilka\EventStore\EventStore;

final class DefaultEventReplayer implements EventReplayer
{
    private $eventStore;
    private $eventDispatcher;

    public function __construct(EventStore $eventStore, EventDispatcher $eventDispatcher)
    {
        $this->eventStore = $eventStore;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function replay(string $id, ?int $from = null, ?int $to = null): void
    {
        if (null !== $from && null !== $to) {
            $events = $this->eventStore->loadFromPlayheadToPlayhead($id, $from, $to);
        } elseif (null !== $from && null === $to) {
            $events = $this->eventStore->loadFromPlayhead($id, $from);
        } else {
            $events = $this->eventStore->load($id);
        }

        $this->replayEvents($events);
    }

    public function replayEvents(array $events): void
    {
        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}
