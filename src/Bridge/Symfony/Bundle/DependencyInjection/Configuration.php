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
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('event_store')->defaultValue(EventStoreInMemory::class)->info('Event store implementation. Default: '.EventStoreInMemory::class)->end()
                ->booleanNode('default_messenger_config')->defaultTrue()->info('Auto-configure Messenger buses')->end()
                ->booleanNode('doctrine_transaction_middleware')->defaultTrue()->info('Add Doctrine transaction middleware')->end()
                ->arrayNode('api_platform')
                    ->info('API Platform bridge')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('expose_cq')->defaultTrue()->info('Expose commands & queries')->end()
                        ->booleanNode('expose_event_store')->defaultTrue()->info('Expose event store (Doctrine only)')->end()
                        ->scalarNode('endpoint_prefix')->defaultValue('cqrs')->info('Route prefix for endpoints')->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}
