<?php

namespace Botilka\Event;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBusInterface;

final class EventDispatcher implements EventDispatcherInterface
{
    private $eventBus;
    private $logger;

    public function __construct(MessageBusInterface $eventBus, LoggerInterface $logger)
    {
        $this->eventBus = $eventBus;
        $this->logger = $logger;
    }

    public function dispatch(Event $event): void
    {
        try {
            $this->eventBus->dispatch($event);
        } catch (NoHandlerForMessageException $e) {
            $this->logger->notice(\sprintf('No handler for "%s".', \get_class($event)));
        }
    }
}
