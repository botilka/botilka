<?php

namespace Botilka\Infrastructure\Symfony\Messenger;

use Botilka\Application\Query\Query;
use Botilka\Application\Query\QueryBus;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerQueryBus implements QueryBus
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch(Query $message)
    {
        return $this->bus->dispatch($message);
    }
}
