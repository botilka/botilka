<?php

namespace Botilka\Bridge\Symfony\Bundle\DependencyInjection;

use Botilka\Infrastructure\InMemory\EventStoreInMemory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('botilka');

        $rootNode
            ->children()
                ->scalarNode('event_store')->defaultValue(EventStoreInMemory::class)->info('Event store implementation. Default: EventStoreInMemory')->end()
                ->scalarNode('default_messenger_config')->defaultTrue()->info('Auto-configure Messenger buses')->end()
                ->booleanNode('doctrine_transaction_middleware')->defaultTrue()->info('Add Doctrine transaction middleware')->end()
                ->arrayNode('api_platform')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('expose_cq')->defaultTrue()->info('Expose commands & queries')->end()
                        ->scalarNode('expose_event_store')->defaultTrue()->info('Expose Event store (Doctrine only).')->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}
