<?php

namespace Botilka\Ui\Console;

use Botilka\Event\EventReplayerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ReplayCommand extends Command
{
    private $replayer;

    public function __construct(EventReplayerInterface $replayer)
    {
        parent::__construct('botilka:replay');
        $this->replayer = $replayer;
    }

    protected function configure()
    {
        $this->setDescription('Replay some/all events for an aggregate.')
            ->addArgument('id', InputArgument::REQUIRED, 'Aggregate ID')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'From playhead (included)')
            ->addOption('to', 't', InputOption::VALUE_OPTIONAL, 'To playhead (included)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string $id */
        $id = $input->getArgument('id');
        $from = $input->getOption('from');
        $to = $input->getOption('to');

        $this->replayer->replay($id, $from, $to);
    }
}
