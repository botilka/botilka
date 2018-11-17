<?php

declare(strict_types=1);

namespace Botilka\Ui\Console;

use Botilka\EventStore\EventStoreManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class EventStoreStatusCommand extends Command
{
    private $eventStoreManager;

    public function __construct(EventStoreManager $eventStoreManager)
    {
        parent::__construct('botilka:event_store:status');
        $this->eventStoreManager = $eventStoreManager;
    }

    protected function configure()
    {
        $this->setDescription('Get some status')
            ->addArgument('context', InputArgument::REQUIRED, 'The status to retrieve (id / domain)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
    }
}
