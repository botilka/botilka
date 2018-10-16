<?php

namespace Botilka\Event;

interface EventDispatcher
{
    public function dispatch(Event $event): void;
}
