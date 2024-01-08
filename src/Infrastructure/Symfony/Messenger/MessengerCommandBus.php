<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\Messenger;

use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandBus;
use Botilka\Application\Command\CommandResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;

final readonly class MessengerCommandBus implements CommandBus
{
    public function __construct(private MessageBusInterface $messageBus) {}

    public function dispatch(Command $message): ?CommandResponse
    {
        $envelope = $this->messageBus->dispatch($message);
        /** @var HandledStamp[] $handledStamps */
        $handledStamps = $envelope->all(HandledStamp::class);

        if (1 === \count($handledStamps)) {
            $result = $handledStamps[0]->getResult();

            if (!$result instanceof CommandResponse) {
                throw new \LogicException(sprintf("Result must for '%s' must be an instance of '%s', '%s' given.", $envelope->getMessage()::class, CommandResponse::class, get_debug_type($result)));
            }

            return $result;
        }

        // async handling, message has been sent
        if ([] !== $envelope->all(SentStamp::class)) {
            return null;
        }

        throw new \LogicException(sprintf('Message of type "%s" was handled 0 or too many times, or was not sent.', $envelope->getMessage()::class));
    }
}
