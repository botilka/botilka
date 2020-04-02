<?php

declare(strict_types=1);

namespace Botilka\Projector;

use Botilka\Event\Event;

interface Projectionist
{
    public function play(Projection $projection): void;

    /**
     * @param array<string, mixed>|null $context
     */
    public function playForEvent(Event $event, ?array $context = []): void;
}
