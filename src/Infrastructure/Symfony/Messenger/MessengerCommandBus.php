<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\Messenger;

use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandBus;
use Botilka\Application\Command\CommandResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;

final class MessengerCommandBus implements CommandBus
{
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

        if (1 === \count($handledStamps)) {
            return $handledStamps[0]->getResult();
        }

        // async handling, message has been sent
        if (\count($envelope->all(SentStamp::class)) > 0) {
            return null;
        }

        throw new \LogicException(\sprintf('Message of type "%s" was handled 0 or too many times, or was not sent.', \get_class($envelope->getMessage())));
    }
}
