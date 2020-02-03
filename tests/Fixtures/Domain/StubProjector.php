<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Domain;

use Botilka\Projector\Projector;

final class StubProjector implements Projector
{
    /** @var bool */
    public $onStubEventPlayed = false;

    public function onStubEvent(StubEvent $event): void
    {
        $this->onStubEventPlayed = true;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            \stdClass::class => 'onStdClass',
            StubEvent::class => 'onStubEvent',
        ];
    }
}
