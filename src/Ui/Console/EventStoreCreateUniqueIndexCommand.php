<?php

namespace Botilka\Ui\Console;

use Botilka\Application\EventStore\EventStoreUniqueIndex;
use Botilka\EventStore\EventStore;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class EventStoreCreateUniqueIndexCommand extends Command
{
    private $eventStores;
    private $projectDir;

    public function __construct(iterable $eventStores, string $projectDir)
    {
        parent::__construct('botilka:event_store:initialize');
        $this->eventStores = $eventStores;
        $this->projectDir = $projectDir;
    }

    protected function configure()
    {
        $this->setDescription('Create unique index for an EventStore implementation if supported. ')
            ->addArgument('implementation', InputArgument::REQUIRED, 'Implementation name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $implementation */
        $implementation = $input->getArgument('implementation');

        $found = null;
        /** @var EventStoreUniqueIndex $eventStore */
        foreach ($this->eventStores as $eventStore) {
            $className = \get_class($eventStore);
            if (false !== \stripos($className, $implementation)) {
                $io->text("Matched: $className");
                $found = true;
                $eventStore->createIndex($this->projectDir);
            }
        }

        if (null === $found) {
            $io->success('Finished.');
        } else {
            $io->warning('No implementation found.');
        }
    }
}
