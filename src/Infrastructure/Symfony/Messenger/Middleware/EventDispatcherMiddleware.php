<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\Messenger\Middleware;

use Botilka\Application\Command\CommandResponse;
use Botilka\Application\Command\EventSourcedCommandResponse;
use Botilka\Event\Event;
use Botilka\Event\EventBus;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Botilka\Projector\Projection;
use Botilka\Projector\Projectionist;
use Botilka\Repository\EventSourcedRepositoryRegistry;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class EventDispatcherMiddleware implements MiddlewareInterface
{
    private $eventStore;
    private $repositoryRegistry;
    private $eventBus;
    private $logger;
    private $projectionist;

    public function __construct(EventStore $eventStore, ContainerInterface $repositoryRegistry, EventBus $eventBus, LoggerInterface $logger, Projectionist $projectionist)
    {
        $this->eventStore = $eventStore;
        $this->repositoryRegistry = $repositoryRegistry;
        $this->eventBus = $eventBus;
        $this->logger = $logger;
        $this->projectionist = $projectionist;
    }

    /**
     * Save event as soon as the command has been handled so if it fails, we won't dispatch non saved events.
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $envelope = $stack->next()->handle($envelope, $stack); // execute the handler first
        /** @var HandledStamp $handledStamp */
        $handledStamp = $envelope->all(HandledStamp::class)[0];
        $commandResponse = $handledStamp->getResult();

        if (!$commandResponse instanceof CommandResponse) {
            throw new \InvalidArgumentException(\sprintf('Result must be an instance of %s, %s given.', CommandResponse::class, \is_object($commandResponse) ? \get_class($commandResponse) : \gettype($commandResponse)));
        }

        $event = $commandResponse->getEvent();

        // persist to event store only if aggregate is event sourced
        if ($commandResponse instanceof EventSourcedCommandResponse) {
            $this->handleEventSourcedCommandResponse($commandResponse, $event);
        }

        try {
            $this->eventBus->dispatch($event);
        } catch (NoHandlerForMessageException $e) {
            $this->logger->notice(\sprintf('No event handler for %s.', \get_class($event)));
        }

        $projection = new Projection($event);
        $this->projectionist->play($projection); // make your projector async if necessary

        return $envelope;
    }

    private function handleEventSourcedCommandResponse(EventSourcedCommandResponse $commandResponse, Event $event): void
    {
        try {
            $aggregateRootClassName = \get_class($commandResponse->getAggregateRoot());
            if ($this->repositoryRegistry->has($aggregateRootClassName)) {
                $this->repositoryRegistry->get($aggregateRootClassName)->save($commandResponse);
            } else {
                $this->eventStore->append($commandResponse->getId(), $commandResponse->getPlayhead(), \get_class($event), $event, null, new \DateTimeImmutable(), $commandResponse->getDomain());
            }
        } catch (EventStoreConcurrencyException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }
}
