<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\EventDispatcher;

use Botilka\Projector\Projection;
use Botilka\Projector\Projectionist;
use Botilka\Projector\Projector;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DefaultProjectionist implements Projectionist, EventSubscriberInterface
{
    private $eventDispatcher;
    private $projectors;

    public function __construct(EventDispatcherInterface $eventDispatcher, iterable $projectors)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->projectors = $projectors;
    }

    public function dispatch(Projection $projection): void
    {
        $event = $projection->getEvent();

        /** @var Projector $projector */
        foreach ($this->projectors as $projector) {
            $eventMap = $projector::getSubscribedEvents();

            if (null === $method = $eventMap[$eventClass = \get_class($event)] ?? null) {
                continue;
            }

            $projector->$method($event);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Projection::class => 'dispatch',
        ];
    }
}
