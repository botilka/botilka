<?php

declare(strict_types=1);

namespace Botilka\Event;

use Botilka\EventStore\EventStore;

/**
 * @internal
 */
final class DefaultEventReplayer implements EventReplayer
{
    private $eventStore;
    private $eventBus;

    public function __construct(EventStore $eventStore, EventBus $eventBus)
    {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
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

        foreach ($events as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
