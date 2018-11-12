<?php

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection;

use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandHandler;
use Botilka\Application\EventStore\EventStoreInitializer;
use Botilka\Application\Query\Query;
use Botilka\Application\Query\QueryHandler;
use Botilka\Bridge\ApiPlatform\Action\CommandEntrypointAction;
use Botilka\Bridge\ApiPlatform\DataProvider\CommandDataProvider;
use Botilka\Bridge\ApiPlatform\DataProvider\QueryDataProvider;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\BotilkaExtension;
use Botilka\Event\EventHandler;
use Botilka\EventStore\EventStore;
use Botilka\Infrastructure\Doctrine\EventStoreDoctrine;
use Botilka\Infrastructure\MongoDB\EventStoreMongoDB;
use Botilka\Infrastructure\Symfony\Messenger\Middleware\EventDispatcherBusMiddleware;
use Botilka\Infrastructure\InMemory\EventStoreInMemory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class BotilkaExtensionTest extends TestCase
{
    private const DEFAULT_CONFIG = [
        [
            'default_messenger_config' => true,
            'doctrine_transaction_middleware' => true,
            'api_platform' => [
                'expose_cq' => true,
                'expose_event_store' => true,
            ],
        ],
    ];

    /** @var BotilkaExtension */
    private $extension;

    public function setUp()
    {
        $this->extension = new BotilkaExtension();
    }

    /**
     * This method doesn't build the prophecy and return it because PHPStan doesn't get it.
     */
    private function addDefaultCalls(ObjectProphecy $containerBuilderProphecy): void
    {
        $containerBuilderProphecy->setParameter('botilka.bridge.api_platform', false)->shouldBeCalled();
        $containerBuilderProphecy->setParameter('botilka.messenger.doctrine_transaction_middleware', false)->shouldBeCalled();
    }

    /** @dataProvider prependWithDefaultMessengerProvider */
    public function testPrependWithDefaultMessenger(string $eventStore, bool $withDoctrineTranslationMiddleware): void
    {
        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $this->addDefaultCalls($containerBuilderProphecy);
        $containerBuilderProphecy->hasExtension('api_platform')->shouldBeCalled();
        $containerBuilderProphecy->getExtensionConfig('botilka')->willReturn(\array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'event_store' => $eventStore,
                'doctrine_transaction_middleware' => $withDoctrineTranslationMiddleware,
                'api_platform' => [
                    'expose_cq' => false,
                    'expose_event_store' => false,
                ],
            ],
        ]))->shouldBeCalled();

        $middleware = [EventDispatcherBusMiddleware::class];

        if ('Botilka\\Infrastructure\\Doctrine\\EventStoreDoctrine' === $eventStore) {
            $containerBuilderProphecy->setParameter('botilka.messenger.doctrine_transaction_middleware', true)->shouldBeCalledTimes((int) $withDoctrineTranslationMiddleware);
            if (true === $withDoctrineTranslationMiddleware) {
                \array_unshift($middleware, 'doctrine_transaction_middleware');
            }
        }

        $containerBuilderProphecy->prependExtensionConfig(
            'framework', [
                'messenger' => [
                    'default_bus' => 'messenger.bus.commands',
                    'buses' => [
                        'messenger.bus.commands' => [
                            'middleware' => $middleware,
                        ],
                        'messenger.bus.queries' => [],
                        'messenger.bus.events' => [],
                    ],
                ],
            ]
        )->shouldBeCalled();

        $this->extension->prepend($containerBuilderProphecy->reveal());
    }

    public function prependWithDefaultMessengerProvider(): array
    {
        return [
            ['Botilka\\Infrastructure\\Doctrine\\EventStoreDoctrine', true],
            ['Botilka\\Infrastructure\\Doctrine\\EventStoreDoctrine', false],
            ['Botilka\\Infrastructure\\InMemory\\EventStoreInMemory', true],
            ['Botilka\\Infrastructure\\InMemory\\EventStoreInMemory', false],
        ];
    }

    public function testPrependWithoutDefaultMessenger(): void
    {
        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $this->addDefaultCalls($containerBuilderProphecy);
        $containerBuilderProphecy->hasExtension('api_platform')->shouldBeCalled();
        $containerBuilderProphecy->getExtensionConfig('botilka')->willReturn(\array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'default_messenger_config' => false,
                'doctrine_transaction_middleware' => false,
                'api_platform' => [
                    'expose_cq' => false,
                    'expose_event_store' => false,
                ],
            ],
        ]))->shouldBeCalled();
        $containerBuilderProphecy->prependExtensionConfig('framework')->shouldNotBeCalled();

        $this->extension->prepend($containerBuilderProphecy->reveal());
    }

    public function testPrependApiPlatform(): void
    {
        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $this->addDefaultCalls($containerBuilderProphecy);
        $containerBuilderProphecy->hasExtension('api_platform')->willReturn(true)->shouldBeCalled();
        $containerBuilderProphecy->getExtensionConfig('botilka')->willReturn(\array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'default_messenger_config' => false,
                'doctrine_transaction_middleware' => false,
                'api_platform' => [
                    'expose_cq' => true,
                    'expose_event_store' => true,
                ],
            ],
        ]))->shouldBeCalled();
        $containerBuilderProphecy->prependExtensionConfig('doctrine', ['orm' => ['mappings' => ['Botilka' => ['is_bundle' => false, 'type' => 'annotation', 'dir' => '%kernel.project_dir%/vendor/botilka/botilka/src/Infrastructure/Doctrine', 'prefix' => 'Botilka\Infrastructure\Doctrine', 'alias' => 'Botilka']]]])->shouldBeCalled();
        $containerBuilderProphecy->prependExtensionConfig('api_platform', ['mapping' => ['paths' => ['%kernel.project_dir%/vendor/botilka/botilka/src/Bridge/ApiPlatform/Resource', '%kernel.project_dir%/vendor/botilka/botilka/src/Infrastructure/Doctrine']]])->shouldBeCalled();
        $containerBuilderProphecy->setParameter('botilka.bridge.api_platform', true)->shouldBeCalled();

        $this->extension->prepend($containerBuilderProphecy->reveal());
    }

    /** @dataProvider loadProvider */
    public function testLoad(string $eventStore, bool $hasApiPlatformBridge, bool $defaultMessengerConfig): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('botilka.bridge.api_platform', $hasApiPlatformBridge);
        $container->setParameter('botilka.messenger.doctrine_transaction_middleware', EventStoreDoctrine::class === $eventStore);

        $configs = \array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'event_store' => $eventStore,
                'default_messenger_config' => $defaultMessengerConfig,
            ],
        ]);

        $this->extension->load($configs, $container);

        $this->assertSame($eventStore, (string) $container->getAlias(EventStore::class));
        $this->assertSame((bool) $container->getParameter('botilka.messenger.doctrine_transaction_middleware'), $container->hasDefinition('messenger.middleware.doctrine_transaction_middleware'));
        $this->assertSame($defaultMessengerConfig, $container->hasDefinition(EventDispatcherBusMiddleware::class));
        $this->assertSame($hasApiPlatformBridge, $container->hasDefinition(DescriptionContainer::class));
        $this->assertSame($hasApiPlatformBridge, $container->hasDefinition(CommandDataProvider::class));
        $this->assertSame($hasApiPlatformBridge, $container->hasDefinition(QueryDataProvider::class));
        $this->assertSame($hasApiPlatformBridge, $container->hasDefinition(CommandEntrypointAction::class));
    }

    public function loadProvider(): array
    {
        return [
            [EventStoreDoctrine::class, true, true],
            [EventStoreDoctrine::class, false, false],
            [EventStoreDoctrine::class, true, false],
            [EventStoreDoctrine::class, false, true],

            [EventStoreInMemory::class, true, true],
            [EventStoreInMemory::class, false, false],
            [EventStoreInMemory::class, true, false],
            [EventStoreInMemory::class, false, true],

            [EventStoreMongoDB::class, true, true],
            [EventStoreMongoDB::class, false, false],
            [EventStoreMongoDB::class, true, false],
            [EventStoreMongoDB::class, false, true],
        ];
    }

    public function testAddTagIfDefaultMessengerConfig(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $tags = [
            CommandHandler::class => ['messenger.message_handler', ['bus' => 'messenger.bus.commands']],
            QueryHandler::class => ['messenger.message_handler', ['bus' => 'messenger.bus.queries']],
            EventHandler::class => ['messenger.message_handler', ['bus' => 'messenger.bus.events']],
            Command::class => ['cqrs.command'],
            Query::class => ['cqrs.query'],
            EventStoreInitializer::class => ['botilka.event_store.initializable'],
        ];
        $count = \count($tags);

        $configs = \array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'default_messenger_config' => true,
            ],
        ]);

        $definition = $this->createMock(ChildDefinition::class);
        $definition->expects($this->exactly($count))
            ->method('addTag')
            ->withConsecutive(...\array_values($tags));

        $container->expects($this->exactly($count))
            ->method('registerForAutoconfiguration')
            ->withConsecutive(...\array_values(\array_map(function ($item) {
                return [$item];
            }, \array_keys($tags))))
            ->willReturn($definition);

        $this->extension->load($configs, $container);
    }

    public function testDontAddTagIfNotDefaultMessengerConfig(): void
    {
        $container = $this->createMock(ContainerBuilder::class);

        $configs = \array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'default_messenger_config' => false,
            ],
        ]);
        $container->expects($this->never())
            ->method('registerForAutoconfiguration');

        $this->extension->load($configs, $container);
    }
}
