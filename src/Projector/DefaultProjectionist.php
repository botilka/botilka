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

    public function __construct(iterable $projectors = [], LoggerInterface $logger)
    {
        $this->projectors = $projectors;
        $this->logger = $logger;
    }

    public function play(Projection $projection): void
    {
        $event = $projection->getEvent();
        $eventClass = \get_class($event);

        $context = $projection->getContext();
        $matching = $context['matching'] ?? null;

        /** @var Projector $projector */
        $found = false;
        foreach ($this->projectors as $projector) {
            $eventMap = $projector::getSubscribedEvents();

            if (null === $method = $eventMap[$eventClass] ?? null) {
                continue;
            }

            if (null !== $matching && 0 === \preg_match(\chr(1).$matching.\chr(1).'i', ($projectorClass = \get_class($projector)).'::'.$method)) {
                $found = true;
                $this->logger->notice(\sprintf('Projection %s::%s skipped.', $projectorClass, $method));
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
