<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection;

use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandHandler;
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
use Botilka\Infrastructure\InMemory\EventStoreInMemory;
use Botilka\Infrastructure\MongoDB\EventStoreMongoDB;
use Botilka\Infrastructure\StoreInitializer;
use Botilka\Infrastructure\Symfony\Messenger\Middleware\EventDispatcherMiddleware;
use Botilka\Projector\Projector;
use Botilka\Repository\EventSourcedRepository;
use Botilka\Ui\Console\EventReplayCommand;
use Botilka\Ui\Console\ProjectorPlayCommand;
use Botilka\Ui\Console\StoreInitializeCommand;
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
                'endpoint_prefix' => 'cqrs',
            ],
        ],
    ];

    /** @var BotilkaExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->extension = new BotilkaExtension();
    }

    /** @dataProvider prependWithDefaultMessengerProvider */
    public function testPrependWithDefaultMessenger(string $eventStore, bool $withDoctrineTranslationMiddleware): void
    {
        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $this->addContainerBuilderDefaultCalls($containerBuilderProphecy);
        $containerBuilderProphecy->hasExtension('api_platform')->shouldBeCalled();
        $containerBuilderProphecy->getExtensionConfig('botilka')->willReturn(\array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'event_store' => $eventStore,
                'doctrine_transaction_middleware' => $withDoctrineTranslationMiddleware,
                'api_platform' => [
                    'expose_cq' => false,
                    'expose_event_store' => false,
                    'endpoint_prefix' => 'cqrs',
                ],
            ],
        ]))->shouldBeCalled();

        $middleware = [EventDispatcherMiddleware::class];

        if (EventStoreDoctrine::class === $eventStore) {
            $containerBuilderProphecy->setParameter('botilka.messenger.doctrine_transaction_middleware', true)->shouldBeCalledTimes((int) $withDoctrineTranslationMiddleware);
            if (true === $withDoctrineTranslationMiddleware) {
                \array_unshift($middleware, 'doctrine_transaction');
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
            [EventStoreDoctrine::class, true],
            [EventStoreDoctrine::class, false],
            [EventStoreInMemory::class, true],
            [EventStoreInMemory::class, false],
            [EventStoreMongoDB::class, true],
            [EventStoreMongoDB::class, false],
        ];
    }

    public function testPrependWithoutDefaultMessenger(): void
    {
        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $this->addContainerBuilderDefaultCalls($containerBuilderProphecy);
        $containerBuilderProphecy->hasExtension('api_platform')->shouldBeCalled();
        $containerBuilderProphecy->getExtensionConfig('botilka')->willReturn(\array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'default_messenger_config' => false,
                'doctrine_transaction_middleware' => false,
                'api_platform' => [
                    'expose_cq' => false,
                    'expose_event_store' => false,
                    'endpoint_prefix' => 'cqrs',
                ],
            ],
        ]))->shouldBeCalled();
        $containerBuilderProphecy->prependExtensionConfig('framework')->shouldNotBeCalled();

        $this->extension->prepend($containerBuilderProphecy->reveal());
    }

    /** @dataProvider prependApiPlatformWithDoctrineProvider */
    public function testPrependApiPlatformWithDoctrine(bool $exposeCQ, bool $exposeEventStore): void
    {
        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $this->addContainerBuilderDefaultCalls($containerBuilderProphecy);
        $containerBuilderProphecy->hasExtension('api_platform')->willReturn(true)->shouldBeCalled();
        $containerBuilderProphecy->getExtensionConfig('botilka')->willReturn(\array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'event_store' => EventStoreDoctrine::class,
                'default_messenger_config' => false,
                'doctrine_transaction_middleware' => false,
                'api_platform' => [
                    'expose_cq' => $exposeCQ,
                    'expose_event_store' => $exposeEventStore,
                    'endpoint_prefix' => 'cqrs',
                ],
            ],
        ]))->shouldBeCalled();

        $paths = [];
        if (true === $exposeCQ) {
            $paths[] = '%kernel.project_dir%/vendor/botilka/botilka/src/Bridge/ApiPlatform/Resource';
            $containerBuilderProphecy->setParameter('botilka.bridge.api_platform', true)->shouldBeCalled();
        }

        if (true === $exposeEventStore) {
            $paths[] = '%kernel.project_dir%/vendor/botilka/botilka/src/Infrastructure/Doctrine';
            $containerBuilderProphecy->prependExtensionConfig('doctrine', ['orm' => ['mappings' => ['Botilka' => ['is_bundle' => false, 'type' => 'annotation', 'dir' => '%kernel.project_dir%/vendor/botilka/botilka/src/Infrastructure/Doctrine', 'prefix' => 'Botilka\Infrastructure\Doctrine', 'alias' => 'Botilka']]]])->shouldBeCalled();
        }

        if (\count($paths) > 0) {
            $containerBuilderProphecy->prependExtensionConfig('api_platform', ['mapping' => ['paths' => $paths]])->shouldBeCalled();
        }

        $this->extension->prepend($containerBuilderProphecy->reveal());
    }

    public function prependApiPlatformWithDoctrineProvider(): array
    {
        return [
            [true, true],
            [false, true],
            [true, false],
            [false, false],
        ];
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

        self::assertSame($eventStore, (string) $container->getAlias(EventStore::class));
        self::assertTrue($container->hasDefinition(StoreInitializeCommand::class));
        self::assertTrue($container->hasDefinition(EventReplayCommand::class));
        self::assertTrue($container->hasDefinition(ProjectorPlayCommand::class));

        self::assertSame((bool) $container->getParameter('botilka.messenger.doctrine_transaction_middleware'), $container->hasDefinition('messenger.middleware.doctrine_transaction'));
        self::assertSame($defaultMessengerConfig, $container->hasDefinition(EventDispatcherMiddleware::class));
        self::assertSame($hasApiPlatformBridge, $container->hasDefinition(DescriptionContainer::class));
        self::assertSame($hasApiPlatformBridge, $container->hasDefinition(CommandDataProvider::class));
        self::assertSame($hasApiPlatformBridge, $container->hasDefinition(QueryDataProvider::class));
        self::assertSame($hasApiPlatformBridge, $container->hasDefinition(CommandEntrypointAction::class));
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
            Projector::class => ['botilka.projector'],
            Command::class => ['cqrs.command'],
            Query::class => ['cqrs.query'],
            StoreInitializer::class => ['botilka.store.initializer'],
            EventSourcedRepository::class => ['botilka.repository.event_sourced'],
        ];
        $count = \count($tags);

        $configs = \array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'default_messenger_config' => true,
            ],
        ]);

        $definition = $this->createMock(ChildDefinition::class);
        $definition->expects(self::exactly($count))
            ->method('addTag')
            ->withConsecutive(...\array_values($tags))
        ;

        $container->expects(self::exactly($count))
            ->method('registerForAutoconfiguration')
            ->withConsecutive(...\array_values(\array_map(static function ($item) {
                return [$item];
            }, \array_keys($tags))))
            ->willReturn($definition)
        ;

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
        $container->expects(self::never())
            ->method('registerForAutoconfiguration')
        ;

        $this->extension->load($configs, $container);
    }

    private function addContainerBuilderDefaultCalls(ObjectProphecy $containerBuilderProphecy): void
    {
        $containerBuilderProphecy->setParameter('botilka.bridge.api_platform', false)->shouldBeCalled();
        $containerBuilderProphecy->setParameter('botilka.messenger.doctrine_transaction_middleware', false)->shouldBeCalled();
    }
}
