<?php

declare(strict_types=1);

namespace Botilka\Projector;

use Botilka\Event\Event;

/**
 * @internal
 */
final class Projection
{
    private $event;
    private $context;

    public function __construct(Event $event, ?array $context = null)
    {
        $this->event = $event;
        $this->context = $context;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}
