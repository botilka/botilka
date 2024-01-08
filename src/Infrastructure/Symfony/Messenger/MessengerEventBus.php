<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\Messenger;

use Botilka\Event\Event;
use Botilka\Event\EventBus;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class MessengerEventBus implements EventBus
{
    public function __construct(private MessageBusInterface $messageBus) {}

    public function dispatch(Event $message): void
    {
        $this->messageBus->dispatch($message);
    }
}
