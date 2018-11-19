<?php

declare(strict_types=1);

namespace Botilka\Application\Command;

use Botilka\Event\Event;

/**
 * Represent a response from a command in 'CQRS' mode, meaning there is no information about the event
 * This event may not be saved, you need to update your aggregate state by yourself.
 */
class CommandResponse
{
    private $id;
    private $event;

    public function __construct(string $id, Event $event)
    {
        $this->id = $id;
        $this->event = $event;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public static function fromValues(string $id, Event $event): self
    {
        return new self($id, $event);
    }
}
