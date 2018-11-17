<?php

declare(strict_types=1);

namespace Botilka\Ui\Console;

use Botilka\Event\EventBus;
use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class EventReplayCommand extends Command
{
    use EventsFromEventStoreManagerCommandTrait;

    private $eventStoreManager;
    private $eventBus;

    public function __construct(EventStoreManager $eventStoreManager, EventBus $eventBus)
    {
        parent::__construct('botilka:event_store:replay');
        $this->eventStoreManager = $eventStoreManager;
        $this->eventBus = $eventBus;
    }

    protected function configure()
    {
        $this->setDescription('Replay some/all events for an aggregate or a domain')
            ->configureDefault($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $managedEvents = $this->getManagedEvents($input);

        /** @var ManagedEvent $managedEvent */
        foreach ($managedEvents as $managedEvent) {
            $this->eventBus->dispatch($managedEvent->getDomainEvent());
        }
    }
}
