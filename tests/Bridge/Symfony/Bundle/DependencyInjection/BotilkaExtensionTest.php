<?php

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection;

use Botilka\Bridge\ApiPlatform\Action\CommandAction;
use Botilka\Bridge\ApiPlatform\DataProvider\CommandDataProvider;
use Botilka\Bridge\ApiPlatform\DataProvider\QueryDataProvider;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\BotilkaExtension;
use Botilka\Event\EventDispatcher;
use Botilka\EventStore\EventStore;
use Botilka\Infrastructure\Doctrine\EventStoreDoctrine;
use Botilka\Infrastructure\EventDispatcherBusMiddleware;
use Botilka\Infrastructure\InMemory\EventStoreInMemory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BotilkaExtensionTest extends TestCase
{
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
    public function testPrependWithDefaultMessenger(bool $withDoctrineTranslationMiddleware)
    {
        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $this->addDefaultCalls($containerBuilderProphecy);
        $containerBuilderProphecy->hasExtension('api_platform')->shouldBeCalled();
        if ($withDoctrineTranslationMiddleware) {
            $containerBuilderProphecy->hasExtension('doctrine')->willReturn(true)->shouldBeCalled();
        }
        $containerBuilderProphecy->getExtensionConfig('botilka')->willReturn([
            [
                'default_messenger_config' => true,
                'doctrine_transaction_middleware' => $withDoctrineTranslationMiddleware,
                'api_platform' => [
                    'expose_cq' => false,
                    'expose_event_store' => false,
                ],
            ],
        ])->shouldBeCalled();
        $containerBuilderProphecy->setParameter('botilka.messenger.doctrine_transaction_middleware', true)->shouldBeCalledTimes((int) $withDoctrineTranslationMiddleware);

        $middleware = [EventDispatcherBusMiddleware::class];
        if (true === $withDoctrineTranslationMiddleware) {
            \array_unshift($middleware, 'doctrine_transaction_middleware');
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
            [true],
            [false],
        ];
    }

    public function testPrependWithoutDefaultMessenger()
    {
        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $this->addDefaultCalls($containerBuilderProphecy);
        $containerBuilderProphecy->hasExtension('api_platform')->shouldBeCalled();
        $containerBuilderProphecy->getExtensionConfig('botilka')->willReturn([
            [
                'default_messenger_config' => false,
                'doctrine_transaction_middleware' => false,
                'api_platform' => [
                    'expose_cq' => false,
                    'expose_event_store' => false,
                ],
            ],
        ])->shouldBeCalled();
        $containerBuilderProphecy->prependExtensionConfig('framework')->shouldNotBeCalled();

        $this->extension->prepend($containerBuilderProphecy->reveal());
    }

    public function testPrependApiPlatform()
    {
        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $this->addDefaultCalls($containerBuilderProphecy);
        $containerBuilderProphecy->hasExtension('api_platform')->willReturn(true)->shouldBeCalled();
        $containerBuilderProphecy->getExtensionConfig('botilka')->willReturn([
            [
                'default_messenger_config' => false,
                'doctrine_transaction_middleware' => false,
                'api_platform' => [
                    'expose_cq' => true,
                    'expose_event_store' => true,
                ],
            ],
        ])->shouldBeCalled();
        $containerBuilderProphecy->prependExtensionConfig('doctrine', ['orm' => ['mappings' => ['Botilka' => ['is_bundle' => false, 'type' => 'annotation', 'dir' => '%kernel.project_dir%/vendor/botilka/botilka/src/Infrastructure/Doctrine', 'prefix' => 'Botilka\Infrastructure\Doctrine', 'alias' => 'Botilka']]]])->shouldBeCalled();
        $containerBuilderProphecy->prependExtensionConfig('api_platform', ['mapping' => ['paths' => ['%kernel.project_dir%/vendor/botilka/botilka/src/Bridge/ApiPlatform/Resource', '%kernel.project_dir%/vendor/botilka/botilka/src/Infrastructure/Doctrine']]])->shouldBeCalled();
        $containerBuilderProphecy->setParameter('botilka.bridge.api_platform', true)->shouldBeCalled();

        $this->extension->prepend($containerBuilderProphecy->reveal());
    }

    /** @dataProvider loadProvider */
    public function testLoad(string $eventStore, bool $hasApiPlatformBridge, bool $defaultMessengerConfig)
    {
        $container = new ContainerBuilder();
        $container->setParameter('botilka.bridge.api_platform', $hasApiPlatformBridge);
        $container->setParameter('botilka.messenger.doctrine_transaction_middleware', EventStoreDoctrine::class === $eventStore);

        $configs = [
            [
                'event_store' => $eventStore,
                'default_messenger_config' => $defaultMessengerConfig,
            ],
        ];

        $this->extension->load($configs, $container);

        $this->assertSame($eventStore, (string) $container->getAlias(EventStore::class));
        $this->assertSame($defaultMessengerConfig, $container->hasDefinition(EventDispatcher::class));
        $this->assertSame($defaultMessengerConfig || EventStoreDoctrine::class === $eventStore, $container->hasDefinition('messenger.middleware.doctrine_transaction_middleware'));
        $this->assertSame($defaultMessengerConfig, $container->hasDefinition(EventDispatcherBusMiddleware::class));
        $this->assertSame($hasApiPlatformBridge, $container->hasDefinition(DescriptionContainer::class));
        $this->assertSame($hasApiPlatformBridge, $container->hasDefinition(CommandDataProvider::class));
        $this->assertSame($hasApiPlatformBridge, $container->hasDefinition(QueryDataProvider::class));
        $this->assertSame($hasApiPlatformBridge, $container->hasDefinition(CommandAction::class));
    }

    public function loadProvider(): array
    {
        return [
            [EventStoreDoctrine::class, true, true],
            [EventStoreDoctrine::class, false, false],
            [EventStoreDoctrine::class, true, false],
            [EventStoreDoctrine::class, false, true],
            [EventStoreInMemory::class, false, false],
        ];
    }

    public function testLoadAddTag()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $count = \count(BotilkaExtension::AUTOCONFIGURAION_CLASSES_TAG);

        $definition = $this->createMock(ChildDefinition::class);
        $definition->expects($this->exactly($count))
            ->method('addTag')
            ->withConsecutive(...\array_values(\array_map(function ($item) {
                return [$item];
            }, BotilkaExtension::AUTOCONFIGURAION_CLASSES_TAG)));

        $container->expects($this->exactly($count))
            ->method('registerForAutoconfiguration')
            ->withConsecutive(...\array_values(\array_map(function ($item) {
                return [$item];
            }, \array_keys(BotilkaExtension::AUTOCONFIGURAION_CLASSES_TAG))))
            ->willReturn($definition);

        $this->extension->load([], $container);
    }
}
