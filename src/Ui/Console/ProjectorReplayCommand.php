<?php

declare(strict_types=1);

namespace Botilka\Ui\Console;

use Botilka\Event\Event;
use Botilka\EventStore\EventStore;
use Botilka\Projector\DefaultProjection;
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
    private $eventStore;
    private $projectionist;
    private $logger;

    public function __construct(EventStore $eventStore, Projectionist $projectionist, LoggerInterface $logger)
    {
        parent::__construct('botilka:projector:replay');
        $this->eventStore = $eventStore;
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

        if (null !== $from && null !== $to) {
            $events = $this->eventStore->loadFromPlayheadToPlayhead($id, $from, $to);
        } elseif (null !== $from && null === $to) {
            $events = $this->eventStore->loadFromPlayhead($id, $from);
        } else {
            $events = $this->eventStore->load($id);
        }

        $io->note(\sprintf('%d events found for %s', \count($events), $id));

        /** @var Event $event */
        foreach ($events as $event) {
            $io->writeln('Projecting: '.\get_class($event));
            $projection = new DefaultProjection($event);

            $this->projectionist->replay($projection);
        }
    }
}
