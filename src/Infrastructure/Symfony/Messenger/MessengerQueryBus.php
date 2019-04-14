<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\Messenger;

use Botilka\Application\Query\Query;
use Botilka\Application\Query\QueryBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class MessengerQueryBus implements QueryBus
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch(Query $message)
    {
        $envelope = $this->bus->dispatch($message);

        /** @var HandledStamp $handledStamp */
        $handledStamp = $envelope->all(HandledStamp::class)[0];

        return $handledStamp->getResult();
    }
}
