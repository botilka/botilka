<?php

declare(strict_types=1);

namespace Botilka\Bridge\Symfony\Bundle\DependencyInjection;

use Botilka\Infrastructure\InMemory\EventStoreInMemory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('botilka');
        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('event_store')->defaultValue(EventStoreInMemory::class)->info('Event store implementation. Default: '.EventStoreInMemory::class)->end()
            ->booleanNode('default_messenger_config')->defaultTrue()->info('Auto-configure Symfony Messenger buses')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
