<?php

declare(strict_types=1);

namespace Botilka\Ui\Console;

use Botilka\Infrastructure\StoreInitializer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('botilka:store:initialize')]
final class StoreInitializeCommand extends Command
{
    /**
     * @param iterable<StoreInitializer> $initializers
     */
    public function __construct(private readonly iterable $initializers = [])
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Initialize an store implementation (create, unique index, ...).')
            ->addArgument('type', InputArgument::REQUIRED, sprintf('Type of store (%s)', implode(', ', StoreInitializer::TYPES)))
            ->addArgument('implementation', InputArgument::REQUIRED, 'Implementation name')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force to recreate the event store. ⚠ You will lost all the data, don\'t use it in production.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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
            $className = $initializer::class;
            if (false === stripos($className, $implementation) || $initializer->getType() !== $type) {
                continue;
            }

            $io->text("Using: {$className}");
            try {
                $initializer->initialize($force);
                $found = true;
            } catch (\RuntimeException $e) {
                $io->error($e->getMessage());
            }
        }

        if (!$found) {
            $io->warning('No initializer found.');

            return 1;
        }

        $io->success('Finished.');

        return 0;
    }
}
