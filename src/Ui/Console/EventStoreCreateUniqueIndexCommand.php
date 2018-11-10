<?php

namespace Botilka\Ui\Console;

use Botilka\Event\EventReplayer;
use Botilka\EventStore\EventStore;
use MongoDB\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class EventStoreCreateUniqueIndexCommand extends Command
{
    private $eventStores;

    /**
     * @param $eventStores EventStore
     */
    public function __construct(\iterable $eventStores)
    {
        parent::__construct('botilka:mongodb:event_store:');
        $this->eventStores = $eventStores;
    }

    protected function configure()
    {
        $this->setDescription('Create unique index for an EventStore implementation if supported. ')
            ->addArgument('implementation', InputArgument::REQUIRED, 'Implementation name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
