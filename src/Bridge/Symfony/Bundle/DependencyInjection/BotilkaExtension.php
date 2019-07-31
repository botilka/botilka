<?php

declare(strict_types=1);

namespace Botilka\Bridge\Symfony\Bundle\DependencyInjection;

use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandHandler;
use Botilka\Application\Query\Query;
use Botilka\Application\Query\QueryHandler;
use Botilka\Event\EventHandler;
use Botilka\Infrastructure\Doctrine\EventStoreDoctrine;
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
        Command::class => ['cqrs.command'],
        Query::class => ['cqrs.query'],
        StoreInitializer::class => ['botilka.store.initializer'],
        EventSourcedRepository::class => ['botilka.repository.event_sourced'],
    ];

    public function prepend(ContainerBuilder $container)
    {
        $botilkaConfig = \array_merge([], ...$container->getExtensionConfig('botilka'));
        $container->setParameter('botilka.bridge.api_platform', false);
        $container->setParameter('botilka.messenger.doctrine_transaction_middleware', false);

        // default is to use Messenger
        if ($botilkaConfig['default_messenger_config'] ?? true) {
            $this->prependDefaultMessengerConfig($container, $botilkaConfig);
        }

        $this->prependApliPlatformConfig($container, $botilkaConfig);
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('botilka.yaml');

        if (true === $container->getParameter('botilka.bridge.api_platform')) {
            $loader->load('bridge_api_platform_cq.yaml');
        }

        if (true === $container->getParameter('botilka.messenger.doctrine_transaction_middleware')) {
            $loader->load('messenger_doctrine_transaction_middleware.yaml');
        }

        if (true === $config['default_messenger_config']) {
            $loader->load('messenger_default_config.yaml');
            foreach (self::AUTOCONFIGURAION_CLASSES_TAG as $className => $tag) {
                $container->registerForAutoconfiguration($className)
                    ->addTag($tag[0], $tag[1] ?? [])
                ;
            }
        }

        $this->loadEventStoreConfig($loader, $config['event_store']);

        $container->setParameter('botilka.api_platform.endpoint_prefix', $config['api_platform']['endpoint_prefix']);
    }

    private function prependDefaultMessengerConfig(ContainerBuilder $container, array $config): void
    {
        $commandBusMiddlewares = [EventDispatcherMiddleware::class];

        // if EventStoreDoctrine is used, add doctrine_transaction middleware by default
        if ('Botilka\\Infrastructure\\Doctrine\\EventStoreDoctrine' === ($config['event_store'] ?? '') && ($config['doctrine_transaction_middleware'] ?? true)) {
            $container->setParameter('botilka.messenger.doctrine_transaction_middleware', true);
            \array_unshift($commandBusMiddlewares, 'doctrine_transaction');
        }

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

    private function prependApliPlatformConfig(ContainerBuilder $container, array $botilkaConfig): void
    {
        if (!$container->hasExtension('api_platform')) {
            return;
        }

        $config = $botilkaConfig['api_platform'] ?? [];

        $paths = [];
        if ($config['expose_cq'] ?? true) {
            $paths[] = '%kernel.project_dir%/vendor/botilka/botilka/src/Bridge/ApiPlatform/Resource';
            $container->setParameter('botilka.bridge.api_platform', true);
        }

        $paths = $this->prependApliPlatformEventStoreConfig($container, $config, $botilkaConfig, $paths);

        if (\count($paths) > 0) {
            $container->prependExtensionConfig('api_platform', [
                'mapping' => [
                    'paths' => $paths,
                ],
            ]);
        }
    }

    private function prependApliPlatformEventStoreConfig(ContainerBuilder $container, array $config, array $botilkaConfig, array $paths): array
    {
        if (($config['expose_event_store'] ?? true) && 'Botilka\\Infrastructure\\Doctrine\\EventStoreDoctrine' === ($botilkaConfig['event_store'] ?? null)) {
            $paths[] = '%kernel.project_dir%/vendor/botilka/botilka/src/Infrastructure/Doctrine';
            $container->prependExtensionConfig('doctrine', [
                'orm' => [
                    'mappings' => [
                        'Botilka' => [
                            'is_bundle' => false,
                            'type' => 'annotation',
                            'dir' => '%kernel.project_dir%/vendor/botilka/botilka/src/Infrastructure/Doctrine',
                            'prefix' => 'Botilka\Infrastructure\Doctrine',
                            'alias' => 'Botilka',
                        ],
                    ],
                ],
            ]);
        }

        return $paths;
    }

    private function loadEventStoreConfig(LoaderInterface $loader, string $eventStore): void
    {
        switch ($eventStore) {
            case 'Botilka\\Infrastructure\\Doctrine\\EventStoreDoctrine':
                $loader->load('event_store_doctrine.yaml');
                break;
            case 'Botilka\\Infrastructure\\MongoDB\\EventStoreMongoDB':
                $loader->load('event_store_mongodb.yaml');
                break;
        }
    }
}
