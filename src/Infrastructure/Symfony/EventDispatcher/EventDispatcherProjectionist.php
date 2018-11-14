<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\EventDispatcher;

use Botilka\Projector\Projection;
use Botilka\Projector\Projectionist;
use Botilka\Projector\Projector;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EventDispatcherProjectionist implements Projectionist, EventSubscriberInterface
{
    private $eventDispatcher;
    private $projectors;
    private $logger;

    public function __construct(EventDispatcherInterface $eventDispatcher, iterable $projectors, LoggerInterface $logger)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->projectors = $projectors;
        $this->logger = $logger;
    }

    public function replay(Projection $projection): void
    {
        $event = $projection->getEvent();
        $eventClass = \get_class($event);

        /** @var Projector $projector */
        $found = false;
        foreach ($this->projectors as $projector) {
            $eventMap = $projector::getSubscribedEvents();

            if (null === $method = $eventMap[$eventClass] ?? null) {
                continue;
            }

            $projector->$method($event);
            $found = true;
        }

        if (false === $found) {
            $this->logger->notice(\sprintf('No projector handler for %s.', $eventClass));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Projection::class => 'replay',
        ];
    }
}
