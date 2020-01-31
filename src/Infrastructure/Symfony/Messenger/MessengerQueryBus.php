<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\Messenger;

use Botilka\Application\Query\Query;
use Botilka\Application\Query\QueryBus;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerQueryBus implements QueryBus
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(Query $message)
    {
        return $this->handle($message);
    }
}
