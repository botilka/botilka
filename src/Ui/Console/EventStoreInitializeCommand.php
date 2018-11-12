<?php

namespace Botilka\Ui\Console;

use Botilka\Application\EventStore\EventStoreInitializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class EventStoreInitializeCommand extends Command
{
    private $initializers;

    public function __construct(iterable $initializers = [])
    {
        parent::__construct('botilka:event_store:initialize');
        $this->initializers = $initializers;
    }

    protected function configure()
    {
        $this->setDescription('Initialize an event store implementation (create, unique index, ...).')
            ->addArgument('implementation', InputArgument::REQUIRED, 'Implementation name')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force to recreate the event store. ⚠ You lost all the data, don\'t use it in production.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string $implementation */
        $implementation = $input->getArgument('implementation');
        /** @var bool $force */
        $force = $input->getOption('force');

        $io = new SymfonyStyle($input, $output);

        $found = false;
        /** @var EventStoreInitializer $initializer */
        foreach ($this->initializers as $initializer) {
            $className = \get_class($initializer);
            if (false === \stripos($className, $implementation)) {
                continue;
            }

            $io->text("Matched: $className");
            try {
                $initializer->initialize($force);
                $found = true;
            } catch (\RuntimeException $e) {
                $io->error($e->getMessage());
            }
        }

        if (true === $found) {
            $io->success('Finished.');
        } else {
            $io->warning('No initializer found.');
        }
    }
}
