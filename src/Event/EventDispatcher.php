<?php

declare(strict_types=1);

namespace Botilka\Event;

interface EventDispatcher
{
    public function dispatch(Event $event): void;
}
