<?php

declare(strict_types=1);

namespace Botilka\Ui\Console;

use Botilka\Infrastructure\StoreInitializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class StoreInitializeCommand extends Command
{
    private $initializers;

    public function __construct(iterable $initializers = [])
    {
        parent::__construct('botilka:store:initialize');
        $this->initializers = $initializers;
    }

    protected function configure()
    {
        $this->setDescription('Initialize an store implementation (create, unique index, ...).')
            ->addArgument('type', InputArgument::REQUIRED, \sprintf('Type of store (%s)', \implode(', ', StoreInitializer::TYPES)))
            ->addArgument('implementation', InputArgument::REQUIRED, 'Implementation name')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force to recreate the event store. ⚠ You will lost all the data, don\'t use it in production.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string $implementation */
        $implementation = $input->getArgument('implementation');
        /** @var string $type */
        $type = $input->getArgument('type');
        /** @var bool $force */
        $force = $input->getOption('force');

        $io = new SymfonyStyle($input, $output);

        $found = false;
        /** @var StoreInitializer $initializer */
        foreach ($this->initializers as $initializer) {
            $className = \get_class($initializer);
            if (false === \stripos($className, $implementation) || $initializer->getType() !== $type) {
                continue;
            }

            $io->text("Using: $className");
            try {
                $initializer->initialize($force);
                $found = true;
            } catch (\RuntimeException $e) {
                $io->error($e->getMessage());
            }
        }

        true === $found ? $io->success('Finished.') : $io->warning('No initializer found.');
    }
}
