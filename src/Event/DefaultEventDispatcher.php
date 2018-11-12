<?php

declare(strict_types=1);

namespace Botilka\Event;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;

final class DefaultEventDispatcher implements EventDispatcher
{
    private $eventBus;
    private $logger;

    public function __construct(EventBus $eventBus, LoggerInterface $logger)
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
