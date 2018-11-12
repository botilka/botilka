<?php

namespace Botilka\Infrastructure\Symfony\Messenger;

use Botilka\Event\Event;
use Botilka\Event\EventBus;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerEventBus implements EventBus
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch(Event $message): void
    {
        $this->bus->dispatch($message);
    }
}
