<?php

namespace Botilka\Infrastructure\Symfony\Messenger;

use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandBus;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerCommandBus implements CommandBus
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch(Command $message)
    {
        return $this->bus->dispatch($message);
    }
}
