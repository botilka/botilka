<?php

declare(strict_types=1);

namespace Botilka\Bridge\Symfony\Bundle\DependencyInjection;

use Botilka\Application\Command\CommandHandler;
use Botilka\Application\Query\QueryHandler;
use Botilka\Event\EventHandler;
use Botilka\EventStore\EventStore;
use Botilka\Infrastructure\Doctrine\EventStoreDoctrine;
use Botilka\Infrastructure\MongoDB\EventStoreMongoDB;
use Botilka\Infrastructure\StoreInitializer;
use Botilka\Infrastructure\Symfony\Messenger\Middleware\EventDispatcherMiddleware;
use Botilka\Projector\Projector;
use Botilka\Repository\EventSourcedRepository;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class BotilkaExtension extends Extension implements PrependExtensionInterface
{
    private const AUTOCONFIGURAION_CLASSES_TAG = [
        CommandHandler::class => ['messenger.message_handler', ['bus' => 'messenger.bus.commands']],
        QueryHandler::class => ['messenger.message_handler', ['bus' => 'messenger.bus.queries']],
        EventHandler::class => ['messenger.message_handler', ['bus' => 'messenger.bus.events']],
        Projector::class => ['botilka.projector'],
        StoreInitializer::class => ['botilka.store.initializer'],
        EventSourcedRepository::class => ['botilka.repository.event_sourced'],
    ];

    public function prepend(ContainerBuilder $container): void
    {
        $botilkaConfig = array_merge([], ...$container->getExtensionConfig('botilka'));

        // default is to use Messenger
        if ($botilkaConfig['default_messenger_config'] ?? true) {
            $this->prependDefaultMessengerConfig($container);
        }
    }

    /**
     * @param array<string, array<string, mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('botilka.yaml');

        if (true === $config['default_messenger_config']) {
            $loader->load('messenger_default_config.yaml');
            foreach (self::AUTOCONFIGURAION_CLASSES_TAG as $className => $tag) {
                $container->registerForAutoconfiguration($className)
                    ->addTag($tag[0], $tag[1] ?? [])
                ;
            }
        }

        $this->loadEventStoreConfig($loader, $config['event_store']);
    }

    private function prependDefaultMessengerConfig(ContainerBuilder $container): void
    {
        $commandBusMiddlewares = [EventDispatcherMiddleware::class];

        $container->prependExtensionConfig('framework', [
            'messenger' => [
                'default_bus' => 'messenger.bus.commands',
                'buses' => [
                    'messenger.bus.commands' => [
                        'middleware' => $commandBusMiddlewares,
                    ],
                    'messenger.bus.queries' => [],
                    'messenger.bus.events' => [],
                ],
            ],
        ]);
    }

    /**
     * @param class-string<EventStore> $eventStore
     */
    private function loadEventStoreConfig(LoaderInterface $loader, string $eventStore): void
    {
        switch ($eventStore) {
            case EventStoreDoctrine::class:
                $loader->load('event_store_doctrine.yaml');
                break;
            case EventStoreMongoDB::class:
                $loader->load('event_store_mongodb.yaml');
                break;
        }
    }
}
