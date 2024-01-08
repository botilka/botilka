<?php

declare(strict_types=1);

namespace Botilka\Ui\Console;

use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @internal
 */
trait GetManagedEventsFromEventStoreTrait
{
    private readonly EventStoreManager $eventStoreManager;

    /**
     * @throws \InvalidArgumentException
     */
    protected function checkDomainOrId(InputInterface $input): void
    {
        /** @var bool $id */
        $id = $input->getOption('id');
        /** @var bool $domain */
        $domain = $input->getOption('domain');

        if ((true === $id && true === $domain) || (false === $id && false === $domain)) {
            throw new \InvalidArgumentException('You must set a domain or an id.');
        }
    }

    private function configureParameters(Command $self): Command
    {
        return $self->addArgument('value', InputArgument::REQUIRED, 'The id or the domain')
            ->addOption('id', 'i', InputOption::VALUE_NONE, 'Aggregate root id')
            ->addOption('domain', 'd', InputOption::VALUE_NONE, 'Domain')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'From playhead (included)')
            ->addOption('to', 't', InputOption::VALUE_OPTIONAL, 'To playhead (included)')
        ;
    }

    /**
     * @return iterable<ManagedEvent>
     */
    private function getManagedEvents(InputInterface $input): iterable
    {
        /** @var string $value */
        $value = $input->getArgument('value');

        /** @var bool $domain */
        $domain = $input->getOption('domain');

        if (false !== $domain) {
            return $this->eventStoreManager->loadByDomain($value);
        }

        /** @var string $from */
        $from = $input->getOption('from');
        $from = (int) $from;

        /** @var string $to */
        $to = $input->getOption('to');
        $to = (int) $to;

        return $this->eventStoreManager->loadByAggregateRootId($value, $from, $to);
    }
}
