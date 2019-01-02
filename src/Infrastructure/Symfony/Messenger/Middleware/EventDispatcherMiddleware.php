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
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

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
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $envelope = $stack->next()->handle($envelope, $stack); // execute the handler first
        /** @var HandledStamp $handledStamp */
        $handledStamp = $envelope->all(HandledStamp::class)[0];
        $result = $handledStamp->getResult();

        if (!$result instanceof CommandResponse) {
            throw new \InvalidArgumentException(\sprintf('Result must be an instance of %s, %s given.', CommandResponse::class, \is_object($result) ? \get_class($result) : \gettype($result)));
        }

        $event = $result->getEvent();

        // persist to event store only if aggregate is event sourced
        if ($result instanceof EventSourcedCommandResponse) {
            try {
                $this->eventStore->append($result->getId(), $result->getPlayhead(), \get_class($event), $event, null, new \DateTimeImmutable(), $result->getDomain());
            } catch (EventStoreConcurrencyException $e) {
                $this->logger->error($e->getMessage());

                throw $e;
            }
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
}
