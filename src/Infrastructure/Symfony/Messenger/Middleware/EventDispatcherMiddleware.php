<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\Messenger\Middleware;

use Botilka\Application\Command\CommandResponse;
use Botilka\Event\EventBus;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Botilka\Projector\Projection;
use Botilka\Projector\Projectionist;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;

final class EventDispatcherMiddleware implements MiddlewareInterface
{
    private $eventStore;
    private $eventBus;
    private $logger;
    private $projectionist;

    public function __construct(EventStore $eventStore, EventBus $eventBus, LoggerInterface $logger, Projectionist $projectionist)
    {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
        $this->logger = $logger;
        $this->projectionist = $projectionist;
    }

    /**
     * Save event as soon as the command has been handled so if it fails, we won't dispatch non saved events.
     */
    public function handle($message, callable $next)
    {
        $result = $next($message); // execute the handler first

        if (!$result instanceof CommandResponse) {
            throw new \InvalidArgumentException(\sprintf('Result must be an instance of %s, %s given.', CommandResponse::class, \is_object($result) ? \get_class($result) : \gettype($result)));
        }

        $event = $result->getEvent();

        try {
            $this->eventStore->append($result->getId(), $result->getPlayhead(), \get_class($event), $event, null, new \DateTimeImmutable(), $result->getDomain());
        } catch (EventStoreConcurrencyException $e) {
            $this->logger->error($e->getMessage());

            return;
        }

        try {
            $this->eventBus->dispatch($event);
        } catch (NoHandlerForMessageException $e) {
            $this->logger->notice(\sprintf('No event handler for %s.', \get_class($event)));
        }

        $projection = new Projection($event);
        $this->projectionist->play($projection); // make your projector async if necessary

        return $result;
    }
}
