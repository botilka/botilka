<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Domain;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Domain\EventSourcedAggregateRootApplier;

final class StubEventSourcedAggregateRoot implements EventSourcedAggregateRoot
{
    use EventSourcedAggregateRootApplier;

    private $foo = 123;

    private $eventMap = [
        StubEvent::class => 'onStubHasStubbed',
    ];

    public function getAggregateRootId(): string
    {
        return 'foo_stub_aggregate_root';
    }

    public function getFoo(): int
    {
        return $this->foo;
    }

    public function onStubHasStubbed(StubEvent $event): EventSourcedAggregateRoot
    {
        $instance = clone $this;
        $instance->foo = $event->getFoo();

        return $instance;
    }
}
