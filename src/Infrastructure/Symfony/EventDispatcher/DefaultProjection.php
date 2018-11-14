<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\EventDispatcher;

use Botilka\Projector\Projection;
use Symfony\Component\EventDispatcher\Event;
use Botilka\Event\Event as DomainEvent;

final class DefaultProjection extends Event implements Projection
{
    private $event;

    public function __construct(DomainEvent $event)
    {
        $this->event = $event;
    }

    public function getEvent(): DomainEvent
    {
        return $this->event;
    }
}
