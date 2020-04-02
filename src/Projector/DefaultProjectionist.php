<?php

declare(strict_types=1);

namespace Botilka\Projector;

use Botilka\Event\Event;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class DefaultProjectionist implements Projectionist
{
    private $logger;
    private $projectors;

    /**
     * @param iterable<Projector> $projectors
     */
    public function __construct(LoggerInterface $logger, iterable $projectors = [])
    {
        $this->logger = $logger;
        $this->projectors = $projectors;
    }

    public function play(Projection $projection): void
    {
        $event = $projection->getEvent();
        $eventClass = \get_class($event);

        $context = $projection->getContext();
        $matching = $context['matching'] ?? null;

        $found = false;
        /** @var Projector $projector */
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

            $projector->{$method}($event);
            $found = true;
        }

        if (false === $found) {
            $this->logger->notice(\sprintf('No projector handler for %s.', $eventClass));
        }
    }

    public function playForEvent(Event $event, ?array $context = []): void
    {
        $projection = new Projection($event, $context);
        $this->play($projection);
    }
}
