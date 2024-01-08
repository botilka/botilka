<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection;

use Botilka\Application\Command\CommandBus;
use Botilka\Application\Command\CommandHandler;
use Botilka\Application\Query\QueryBus;
use Botilka\Application\Query\QueryHandler;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\BotilkaExtension;
use Botilka\Event\EventBus;
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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
#[CoversClass(BotilkaExtension::class)]
final class BotilkaExtensionTest extends TestCase
{
    use ProphecyTrait;
    private const DEFAULT_CONFIG = [
        [
            'default_messenger_config' => true,
        ],
    ];

    private BotilkaExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new BotilkaExtension();
    }

    #[DataProvider('providePrependWithDefaultMessengerCases')]
    public function testPrependWithDefaultMessenger(string $eventStore, bool $withDoctrineTranslationMiddleware): void
    {
        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $containerBuilderProphecy->getExtensionConfig('botilka')->willReturn(array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'event_store' => $eventStore,
            ],
        ]))->shouldBeCalled();

        $middleware = [EventDispatcherMiddleware::class];

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

    public static function providePrependWithDefaultMessengerCases(): iterable
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
        $containerBuilderProphecy->getExtensionConfig('botilka')->willReturn(array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'default_messenger_config' => false,
            ],
        ]))->shouldBeCalled();
        $containerBuilderProphecy->prependExtensionConfig('framework')->shouldNotBeCalled();

        $this->extension->prepend($containerBuilderProphecy->reveal());
    }

    #[DataProvider('provideLoadCases')]
    public function testLoad(string $eventStore, bool $defaultMessengerConfig): void
    {
        $container = new ContainerBuilder();

        $configs = array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'event_store' => $eventStore,
                'default_messenger_config' => $defaultMessengerConfig,
            ],
        ]);

        $this->extension->load($configs, $container);

        if (true === $defaultMessengerConfig) {
            self::assertTrue($container->has(EventBus::class));
            self::assertTrue($container->has(CommandBus::class));
            self::assertTrue($container->has(QueryBus::class));
        }

        self::assertSame($eventStore, (string) $container->getAlias(EventStore::class));
        self::assertTrue($container->hasDefinition(StoreInitializeCommand::class));
        self::assertTrue($container->hasDefinition(EventReplayCommand::class));
        self::assertTrue($container->hasDefinition(ProjectorPlayCommand::class));
    }

    public static function provideLoadCases(): iterable
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

    public function testAddTagIfDefaultMessengerConfig(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $tags = [
            CommandHandler::class => ['messenger.message_handler', ['bus' => 'messenger.bus.commands']],
            QueryHandler::class => ['messenger.message_handler', ['bus' => 'messenger.bus.queries']],
            EventHandler::class => ['messenger.message_handler', ['bus' => 'messenger.bus.events']],
            Projector::class => ['botilka.projector'],
            StoreInitializer::class => ['botilka.store.initializer'],
            EventSourcedRepository::class => ['botilka.repository.event_sourced'],
        ];
        $count = \count($tags);

        $configs = array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'default_messenger_config' => true,
            ],
        ]);

        $container->expects(self::exactly($count))
            ->method('registerForAutoconfiguration')
            ->willReturnCallback(function ($className) use ($tags) {
                $definition = $this->createMock(ChildDefinition::class);
                $definition->expects(self::exactly(1))
                    ->method('addTag')
                    ->with($tags[$className][0], $tags[$className][1] ?? [])
                ;

                return $definition;
            })
        ;

        $this->extension->load($configs, $container);
    }

    public function testDontAddTagIfNotDefaultMessengerConfig(): void
    {
        $container = $this->createMock(ContainerBuilder::class);

        $configs = array_merge_recursive(self::DEFAULT_CONFIG, [
            [
                'default_messenger_config' => false,
            ],
        ]);
        $container->expects(self::never())
            ->method('registerForAutoconfiguration')
        ;

        $this->extension->load($configs, $container);
    }
}
