<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\Messenger;

use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandBus;
use Botilka\Application\Command\CommandResponse;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class MessengerCommandBus implements CommandBus
{
    use HandleTrait;

    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(Command $message): ?CommandResponse
    {
        $envelope = $this->messageBus->dispatch($message);
        /** @var HandledStamp[] $handledStamps */
        $handledStamps = $envelope->all(HandledStamp::class);

        $countHandled = \count($handledStamps);
        if (0 === $countHandled) {
            return null;
        }

        return $handledStamps[0]->getResult();
    }
}
