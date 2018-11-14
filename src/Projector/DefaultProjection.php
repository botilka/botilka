<?php

declare(strict_types=1);

namespace Botilka\Projector;

use Botilka\Event\Event;

final class DefaultProjection implements Projection
{
    private $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }
}
