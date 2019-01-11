<?php

declare(strict_types=1);

namespace Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler;

use Botilka\Repository\EventSourcedRepositoryRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EventSourcedRepositoryRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $serviceIds = $container->findTaggedServiceIds('botilka.repository.event_sourced');
        $services = [];

        $registryDef = $container->getDefinition(EventSourcedRepositoryRegistry::class);

        foreach ($serviceIds as $serviceId => $tags) {
            $repositoryDef = $container->getDefinition($serviceId);

            $argumentNames = \array_keys($repositoryDef->getArguments());
            if (!\in_array('$aggregateRootClassName', $argumentNames, true)) {
                $container->log($this, "Skipped: repository '$serviceId' don't have an argument named '\$aggregateRootClassName'.");
                continue;
            }

            $aggregateRootClassName = $repositoryDef->getArgument('$aggregateRootClassName');
            $container->log($this, "Adding to the registry the repository '$serviceId' for '$aggregateRootClassName'.");
            $services[$aggregateRootClassName] = $repositoryDef;
        }

        $registryDef->setArgument('$services', $services);
    }
}
