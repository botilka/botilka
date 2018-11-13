<?php

declare(strict_types=1);

namespace Botilka\Projector;

use Botilka\Event\Event;

interface Projectionist
{
    public function replay(Event $event, ?string $regex = null): void;
}
