<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\Messenger;

use Botilka\Event\Event;
use Botilka\Event\EventBus;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerEventBus implements EventBus
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(Event $message): void
    {
        $this->messageBus->dispatch($message);
    }
}
