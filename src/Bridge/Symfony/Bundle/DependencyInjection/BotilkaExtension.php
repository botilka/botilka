<?php

namespace Botilka\Bridge\Symfony\Bundle\DependencyInjection;

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use Botilka\Command\Command;
use Botilka\Command\CommandHandler;
use Botilka\Event\EventHandler;
use Botilka\Infrastructure\Doctrine\EventStoreDoctrine;
use Botilka\Query\Query;
use Botilka\Query\QueryHandler;
use Doctrine\ORM\Version;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class BotilkaExtension extends Extension implements PrependExtensionInterface
{
    const AUTOCONFIGURAION_CLASSES_TAG = [
        CommandHandler::class => 'messenger.message_handler',
        QueryHandler::class => 'messenger.message_handler',
        EventHandler::class => 'messenger.message_handler',
        Command::class => 'cqrs.command',
        Query::class => 'cqrs.query',
    ];

    public function prepend(ContainerBuilder $container)
    {
        $botilkaConfig = $container->getExtensionConfig('botilka');
        $hasDoctrine = \class_exists(Version::class);

        $setupDefaultMessenger = true;
        $addDoctrineTransactionMiddleware = true;
        $apiPlaformConfig = [];
        foreach ($botilkaConfig as $config) {
            if (isset($config['default_messenger_config']) && false === $config['default_messenger_config']) {
                $setupDefaultMessenger = false;
            }
            if (isset($config['doctrine_transaction_middleware']) && false === $config['doctrine_transaction_middleware']) {
                $addDoctrineTransactionMiddleware = false;
            }
            if (isset($config['api_platform'])) {
                $apiPlaformConfig = $config['api_platform'];
            }
        }

        if (true === $setupDefaultMessenger) {
            $this->prependDefaultMessengerConfig($container, $addDoctrineTransactionMiddleware && $hasDoctrine);
        }

        $this->prependApliPlatformConfig($container, $apiPlaformConfig, $hasDoctrine);
    }

    private function prependApliPlatformConfig(ContainerBuilder $container, array $config, bool $hasDoctrine): void
    {
        if (!\class_exists(ApiPlatformBundle::class)) {
            return;
        }

        $container->setParameter('botilka.bridge.api_platform', false);

        $paths = [];
        if ($config['expose_cq'] ?? false) {
            $paths[] = '%kernel.project_dir%/vendor/botilka/botilka/src/Bridge/ApiPlatform/Resource';
            $container->setParameter('botilka.bridge.api_platform', true);
        }

        if ($config['expose_event_store'] ?? false) {
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

        if (\count($paths) > 0) {
            $container->prependExtensionConfig('api_platform', [
                'mapping' => [
                    'paths' => $paths,
                ],
            ]);
        }
    }

    private function prependDefaultMessengerConfig(ContainerBuilder $container, bool $addDoctrineTransactionMiddleware): void
    {
        $commandBusMiddleware = ['Botilka\Infrastructure\EventDispatcherBusMiddleware'];
        if (true === $addDoctrineTransactionMiddleware) {
            \array_unshift($commandBusMiddleware, 'doctrine_transaction_middleware');
        }

        $container->prependExtensionConfig('framework', [
            'messenger' => [
                'default_bus' => 'messenger.bus.commands',
                'buses' => [
                    'messenger.bus.commands' => [
                        'middleware' => $commandBusMiddleware,
                    ],
                    'messenger.bus.queries' => [],
                    'messenger.bus.events' => [],
                ],
            ],
        ]);
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('event_store_in_memory.yaml');

        if (true === $container->getParameter('botilka.bridge.api_platform')) {
            $loader->load('bridge_api_platform.yaml');
        }

        if (true === $config['default_messenger_config']) {
            $loader->load('default_botilka_config.yaml');
        }

        if (EventStoreDoctrine::class === $config['event_store']) {
            $loader->load('messenger_doctrine_transaction_middleware.yaml');
        }

        foreach (self::AUTOCONFIGURAION_CLASSES_TAG as $className => $tagName) {
            $container->registerForAutoconfiguration($className)
                ->addTag($tagName);
        }
    }
}
