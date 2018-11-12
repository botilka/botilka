<?php

declare(strict_types=1);

namespace Botilka\Application\Command;

use Botilka\Event\Event;

final class CommandResponse
{
    private $id;
    private $event;
    private $playhead;

    public function __construct(string $id, int $playhead, Event $event)
    {
        $this->id = $id;
        $this->playhead = $playhead;
        $this->event = $event;
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

    public static function withValue(string $id, int $playhead, Event $event): self
    {
        return new self($id, $playhead, $event);
    }
}
