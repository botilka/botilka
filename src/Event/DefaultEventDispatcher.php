<?php

namespace Botilka\Event;

use Botilka\Bus\Bus;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;

final class DefaultEventDispatcher implements EventDispatcher
{
    private $eventBus;
    private $logger;

    public function __construct(Bus $eventBus, LoggerInterface $logger)
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
