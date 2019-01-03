<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\Messenger;

use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandBus;
use Botilka\Application\Command\CommandResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class MessengerCommandBus implements CommandBus
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch(Command $message): CommandResponse
    {
        $envelope = $this->bus->dispatch($message);

        /** @var HandledStamp $handledStamp */
        $handledStamp = $envelope->all(HandledStamp::class)[0];

        return $handledStamp->getResult();
    }
}
