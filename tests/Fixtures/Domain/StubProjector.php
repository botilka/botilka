<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Domain;

use Botilka\Projector\Projector;

final class StubProjector implements Projector
{
    public $onStubEventPlayed = false;

    public function onStubEvent(StubEvent $event): void
    {
        $this->onStubEventPlayed = true;
    }

    public static function getSubscribedEvents()
    {
        return [
            \stdClass::class => 'onStdClass',
            StubEvent::class => 'onStubEvent',
        ];
    }
}
