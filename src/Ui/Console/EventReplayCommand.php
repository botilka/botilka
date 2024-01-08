<?php

declare(strict_types=1);

namespace Botilka\Ui\Console;

use Botilka\Event\EventBus;
use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('botilka:event_store:replay')]
final class EventReplayCommand extends Command
{
    use GetManagedEventsFromEventStoreTrait;

    public function __construct(EventStoreManager $eventStoreManager, private readonly EventBus $eventBus)
    {
        parent::__construct();
        $this->eventStoreManager = $eventStoreManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Replay events for an aggregate or a domain')
            ->configureParameters($this)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->checkDomainOrId($input);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $managedEvents = $this->getManagedEvents($input);

        /** @var ManagedEvent $managedEvent */
        foreach ($managedEvents as $managedEvent) {
            $this->eventBus->dispatch($managedEvent->getDomainEvent());
        }

        return 0;
    }
}
