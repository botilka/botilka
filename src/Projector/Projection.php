<?php

declare(strict_types=1);

namespace Botilka\Projector;

use Botilka\Event\Event;

/**
 * @internal
 */
final readonly class Projection
{
    /**
     * @param array<string, mixed>|null $context
     */
    public function __construct(
        private Event $event,
        private ?array $context = []
    ) {}

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
