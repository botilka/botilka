<?php

declare(strict_types=1);

namespace Botilka\Projector;

use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class DefaultProjectionist implements Projectionist
{
    private $projectors;
    private $logger;

    public function __construct(iterable $projectors, LoggerInterface $logger)
    {
        $this->projectors = $projectors;
        $this->logger = $logger;
    }

    public function play(Projection $projection): void
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
}
