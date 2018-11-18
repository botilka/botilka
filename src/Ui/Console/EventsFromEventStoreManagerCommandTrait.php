<?php

declare(strict_types=1);

namespace Botilka\Ui\Console;

use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
trait EventsFromEventStoreManagerCommandTrait
{
    private function configureDefault(self $self): self
    {
        return $self->addArgument('target', InputArgument::REQUIRED, \sprintf('Target to load (%s)', \implode(', ', EventStoreManager::TARGETS)))
            ->addArgument('value', InputArgument::REQUIRED, 'Value to use')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'From playhead (included)')
            ->addOption('to', 't', InputOption::VALUE_OPTIONAL, 'To playhead (included)');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        /** @var string $target */
        $target = $input->getArgument('target');

        if (!\in_array($target, EventStoreManager::TARGETS, true)) {
            throw new \InvalidArgumentException(\sprintf('Given target value \'%s\' is not one of %s.', $target, \implode(', ', EventStoreManager::TARGETS)));
        }
    }

    /**
     * @return ManagedEvent[]
     */
    private function getManagedEvents(InputInterface $input): array
    {
        /** @var string $target */
        $target = $input->getArgument('target');
        $value = $input->getArgument('value');

        if (EventStoreManager::TARGET_DOMAIN === $target) {
            return $this->eventStoreManager->loadByDomain($value);
        }

        $from = $input->getOption('from');
        $to = $input->getOption('to');

        return $this->eventStoreManager->loadByAggregateRootId($value, $from, $to);
    }
}
