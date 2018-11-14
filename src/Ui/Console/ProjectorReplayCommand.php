<?php

declare(strict_types=1);

namespace Botilka\Ui\Console;

use Botilka\Event\Event;
use Botilka\EventStore\EventStore;
use Botilka\Infrastructure\Symfony\EventDispatcher\DefaultProjection;
use Botilka\Projector\Projectionist;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ProjectorReplayCommand extends Command
{
    private $eventStore;
    private $projectionist;

    public function __construct(EventStore $eventStore, Projectionist $projectionist)
    {
        parent::__construct('botilka:projector:replay');
        $this->eventStore = $eventStore;
        $this->projectionist = $projectionist;
    }

    protected function configure()
    {
        $this->setDescription('Re build projections for an aggregate')
            ->addArgument('id', InputArgument::REQUIRED, 'Aggregate ID')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'From playhead (included)')
            ->addOption('to', 't', InputOption::VALUE_OPTIONAL, 'To playhead (included)')
            ->addOption('regex', 'r', InputOption::VALUE_OPTIONAL, 'Filter the projector FQCN with a regex');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string $id */
        $id = $input->getArgument('id');
        $from = $input->getOption('from');
        $to = $input->getOption('to');
        $regex = $input->getOption('regex');

        if (null !== $from && null !== $to) {
            $events = $this->eventStore->loadFromPlayheadToPlayhead($id, $from, $to);
        } elseif (null !== $from && null === $to) {
            $events = $this->eventStore->loadFromPlayhead($id, $from);
        } else {
            $events = $this->eventStore->load($id);
        }

        /** @var Event $event */
        foreach ($events as $event) {
            $projection = new DefaultProjection($event);
            $this->projectionist->dispatch($projection);
        }
    }
}
