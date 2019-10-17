<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Domain;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Domain\EventSourcedAggregateRootApplier;

final class StubEventSourcedAggregateRoot implements EventSourcedAggregateRoot
{
    use EventSourcedAggregateRootApplier;

    protected $eventMap = [
        StubEvent::class => 'stubbed',
    ];

    private $foo = 123;

    public function getAggregateRootId(): string
    {
        return 'foo-bar-baz';
    }

    /**
     * Used to tests if onStubHasStubbed has been called.
     */
    public function getFoo(): int
    {
        return $this->foo;
    }

    public function stub(int $foo): array
    {
        $event = new StubEvent($foo);

        return [
            $this->apply($event),
            $event,
        ];
    }

    private function stubbed(StubEvent $event): EventSourcedAggregateRoot
    {
        $instance = clone $this;
        $instance->foo = $event->getFoo();

        return $instance;
    }
}
