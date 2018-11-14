<?php

declare(strict_types=1);

namespace Botilka\Ui\Console;

use Botilka\Event\Event;
use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;
use Botilka\Projector\Projection;
use Botilka\Projector\Projectionist;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ProjectorReplayCommand extends Command
{
    private $eventStoreManager;
    private $projectionist;
    private $logger;

    public function __construct(EventStoreManager $eventStoreManager, Projectionist $projectionist, LoggerInterface $logger)
    {
        parent::__construct('botilka:projector:replay');
        $this->eventStoreManager = $eventStoreManager;
        $this->projectionist = $projectionist;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this->setDescription('Re build projections for an aggregate')
            ->addArgument('id', InputArgument::REQUIRED, 'Aggregate ID')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'From playhead (included)')
            ->addOption('to', 't', InputOption::VALUE_OPTIONAL, 'To playhead (included)')
            ->addOption('matching', 'm', InputOption::VALUE_OPTIONAL, 'User projector FQCN that matches (regex)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $id */
        $id = $input->getArgument('id');
        $from = $input->getOption('from');
        $to = $input->getOption('to');
        $matching = $input->getOption('matching');

        $events = $this->eventStoreManager->load($id, $from, $to);

        $io->note(\sprintf('%d events found for %s', \count($events), $id));

        /** @var ManagedEvent $event */
        foreach ($events as $event) {
            $domainEvent = $event->getDomainEvent();

            $io->text(\sprintf('%s (%6d): %s (%s)', $event->getRecordedOn()->format('Y-m-d H:i:s'), $event->getPlayhead(), \get_class($domainEvent), \json_encode($event->getMetadata())));
            $projection = new Projection($domainEvent);

            $this->projectionist->play($projection);
        }
    }
}
