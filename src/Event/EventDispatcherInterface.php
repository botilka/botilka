<?php

namespace Botilka\Event;

interface EventDispatcherInterface
{
    public function dispatch(Event $event): void;
}
