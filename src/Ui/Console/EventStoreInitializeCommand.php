<?php

namespace Botilka\Ui\Console;

use Botilka\Application\EventStore\EventStoreInitializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class EventStoreInitializeCommand extends Command
{
    private $initializers;
    private $projectDir;

    public function __construct(iterable $initializers)
    {
        parent::__construct('botilka:event_store:initialize');
        $this->initializers = $initializers;
    }

    protected function configure()
    {
        $this->setDescription('Initializer an event store implementation (create, unique index, ...).')
            ->addArgument('implementation', InputArgument::REQUIRED, 'Implementation name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $implementation */
        $implementation = $input->getArgument('implementation');

        $found = false;
        /** @var EventStoreInitializer $initializer */
        foreach ($this->initializers as $initializer) {
            $className = \get_class($initializer);
            if (false !== \stripos($className, $implementation)) {
                $io->text("Matched: $className");
                $found = true;
                $initializer->initialize();
            }
        }

        if (true === $found) {
            $io->success('Finished.');
        } else {
            $io->warning('No initializer found.');
        }
    }
}
