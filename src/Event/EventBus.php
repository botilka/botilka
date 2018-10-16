<?php

namespace Botilka\Event;

interface EventBus
{
    public function dispatch(Event $event);
}
