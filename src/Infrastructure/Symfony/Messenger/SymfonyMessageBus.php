<?php


namespace Botilka\Infrastructure\Symfony\Messenger;

use Botilka\Bus\Bus;
use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyMessageBus implements Bus
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch($message)
    {
        return $this->bus->dispatch($message);
    }
}
