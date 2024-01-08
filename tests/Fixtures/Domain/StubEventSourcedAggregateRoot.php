<?php

declare(strict_types=1);

namespace Botilka\Tests\Fixtures\Domain;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Domain\EventSourcedAggregateRootApplier;
use Botilka\Event\Event;

final class StubEventSourcedAggregateRoot implements EventSourcedAggregateRoot
{
    use EventSourcedAggregateRootApplier;

    /**
     * @var array<string, string>
     */
    protected $eventMap = [
        StubEvent::class => 'stubbed',
    ];

    private int $foo = 123;

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

    /**
     * @return array{0: self, 1: Event}
     */
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
