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
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class EventDispatcherMiddleware implements MiddlewareInterface
{
    public function __construct(
        private EventStore $eventStore,
        private EventSourcedRepositoryRegistry $repositoryRegistry,
        private EventBus $eventBus,
        private LoggerInterface $logger,
        private Projectionist $projectionist,
    ) {}

    /**
     * Save event as soon as the command has been handled so if it fails, we won't dispatch non saved events.
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $envelope = $stack->next()->handle($envelope, $stack); // execute the handler first
        /** @var HandledStamp[] $handledStamps */
        $handledStamps = $envelope->all(HandledStamp::class);

        if (1 !== \count($handledStamps)) {
            return $envelope;
        }

        $commandResponse = $handledStamps[0]->getResult();

        if (!$commandResponse instanceof CommandResponse) {
            throw new \InvalidArgumentException(sprintf('Result must be an instance of %s, %s given.', CommandResponse::class, get_debug_type($commandResponse)));
        }

        $event = $commandResponse->getEvent();

        // persist to event store only if aggregate is event sourced
        if ($commandResponse instanceof EventSourcedCommandResponse) {
            $this->handleEventSourcedCommandResponse($commandResponse);
        }

        try {
            $this->eventBus->dispatch($event);
        } catch (NoHandlerForMessageException) {
            $this->logger->notice(sprintf('No event handler for %s.', $event::class));
        }

        $projection = new Projection($event);
        $this->projectionist->play($projection); // make your projector async if necessary

        return $envelope;
    }

    private function handleEventSourcedCommandResponse(EventSourcedCommandResponse $commandResponse): void
    {
        $event = $commandResponse->getEvent();

        try {
            $aggregateRootClassName = $commandResponse->getAggregateRoot()::class;
            if ($this->repositoryRegistry->has($aggregateRootClassName)) {
                $this->repositoryRegistry->get($aggregateRootClassName)->save($commandResponse);
            } else {
                $this->eventStore->append($commandResponse->getId(), $commandResponse->getPlayhead(), $event::class, $event, null, new \DateTimeImmutable(), $commandResponse->getDomain());
            }
        } catch (EventStoreConcurrencyException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }
}
