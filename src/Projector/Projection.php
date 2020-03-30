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

    /**
     * @param array<string, mixed>|null $context
     */
    public function __construct(Event $event, ?array $context = [])
    {
        $this->event = $event;
        $this->context = $context;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }
}
