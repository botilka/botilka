<?php

declare(strict_types=1);

namespace Botilka\Projector;

use Botilka\Event\Event;

final class DefaultProjectionist implements Projectionist
{
    private $locator;

    public function __construct(ProjectorLocator $locator)
    {
        $this->locator = $locator;
    }

    public function replay(Event $event, ?string $regex = null): void
    {
        $projectors = $this->locator->get(\get_class($event));

        /** @var Projector&callable $projector */
        foreach ($projectors as $projector) {
            if (null !== $regex && !\preg_match(\chr(1).$regex.\chr(1).'i', \get_class($projector))) {
                continue;
            }

            if (!is_callable($event)) {
                throw new \InvalidArgumentException(sprintf('Projector %s is not callable.', \get_class($projector)));
            }

            $projector($event); // in messenger implementation, $projector is a callable
        }
    }
}
