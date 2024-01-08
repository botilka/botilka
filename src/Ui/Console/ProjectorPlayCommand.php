<?php

declare(strict_types=1);

namespace Botilka\Ui\Console;

use Botilka\EventStore\EventStoreManager;
use Botilka\Projector\Projection;
use Botilka\Projector\Projectionist;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('botilka:projectors:play')]
final class ProjectorPlayCommand extends Command
{
    use GetManagedEventsFromEventStoreTrait;

    public function __construct(EventStoreManager $eventStoreManager, private readonly Projectionist $projectionist)
    {
        parent::__construct();
        $this->eventStoreManager = $eventStoreManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Play projections for an aggregate or a domain')
            ->configureParameters($this)
            ->addOption('matching', 'm', InputOption::VALUE_OPTIONAL, 'Use projector FQCN that matches (regex)')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->checkDomainOrId($input);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $managedEvents = $this->getManagedEvents($input);

        $context = [
            'matching' => $input->getOption('matching'),
        ];

        foreach ($managedEvents as $managedEvent) {
            $domainEvent = $managedEvent->getDomainEvent();

            $io->text(
                sprintf('%s (%6d): %s (%s)',
                    $managedEvent->getRecordedOn()->format('Y-m-d H:i:s'), $managedEvent->getPlayhead(), $domainEvent::class, json_encode($managedEvent->getMetadata(), \JSON_THROW_ON_ERROR))
            );
            $projection = new Projection($domainEvent, $context);

            $this->projectionist->play($projection);
        }

        return 0;
    }
}
