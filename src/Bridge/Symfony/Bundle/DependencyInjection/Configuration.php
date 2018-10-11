<?php

namespace Botilka\Bridge\Symfony\Bundle\DependencyInjection;

use Botilka\Infrastructure\Doctrine\EventStoreDoctrine;
use Botilka\Infrastructure\InMemory\EventStoreInMemory;
use Doctrine\ORM\Version;
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
                ->scalarNode('event_store')->defaultValue(\class_exists(Version::class) ? EventStoreDoctrine::class : EventStoreInMemory::class)->info('EventStore to use. Doctrine if available')->end()
                ->scalarNode('default_messenger_config')->defaultTrue()->info('Auto-configure Messenger buses')->end()
                ->booleanNode('doctrine_transaction_middleware')->defaultTrue()->info('Add Doctrine transaction middleware')->end()
                ->arrayNode('api_platform')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('expose_cq')->defaultTrue()->info('Expose commands & queries')->end()
                        ->scalarNode('expose_event_store')->defaultTrue()->info('Expose EventStore')->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}
