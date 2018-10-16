<?php

namespace Botilka\Infrastructure;

use Botilka\Command\CommandResponse;
use Botilka\Event\EventDispatcherInterface;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;

final class EventDispatcherBusMiddleware implements MiddlewareInterface
{
    private $eventStore;
    private $eventDispatcher;
    private $logger;

    public function __construct(EventStore $eventStore, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        $this->eventStore = $eventStore;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * Save event as soon as the command has been handled so if it fails, we won't dispatch non saved events.
     */
    public function handle($message, callable $next)
    {
        $result = $next($message); // execute the handler first

        if (!$result instanceof CommandResponse) {
            return $result;
        }

        $event = $result->getEvent();

        try {
            $this->eventStore->append($result->getId(), $result->getPlayhead(), \get_class($event), $event, null, new \DateTimeImmutable());
        } catch (EventStoreConcurrencyException $e) {
            $this->logger->error($e->getMessage());

            return;
        }

        $this->eventDispatcher->dispatch($event);

        return $result;
    }
}
