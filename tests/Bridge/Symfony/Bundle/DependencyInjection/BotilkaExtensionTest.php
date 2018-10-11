<?php

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection;

use ApiPlatform\Core\Bridge\Symfony\Bundle\DependencyInjection\ApiPlatformExtension;
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
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BotilkaExtensionTest extends TestCase
{
    public function testPrependWithApiPlatform()
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new ApiPlatformExtension());
        $extension = new BotilkaExtension();
        $extension->prepend($container);

        $this->assertSame([], $container->getExtensionConfig('api_platform'));
    }

    public function testPrependWithoutApiPlatform()
    {
        $container = new ContainerBuilder();
        $extension = new BotilkaExtension();
        $extension->prepend($container);

        $this->assertSame([], $container->getExtensionConfig('api_platform'));
    }

    /** @dataProvider loadProvider */
    public function testLoad(string $eventStore, bool $hasApiPlatformBridge, bool $defaultMessengerConfig)
    {
        $container = new ContainerBuilder();
        $container->setParameter('botilka.bridge.api_platform', $hasApiPlatformBridge);

        $configs = [
            [
                'event_store' => $eventStore,
                'default_messenger_config' => $defaultMessengerConfig,
            ],
        ];

        $extension = new BotilkaExtension();
        $extension->load($configs, $container);

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

        $definition = $this->createMock(ChildDefinition::class);
        $definition->expects($this->exactly(5))
            ->method('addTag')
            ->withConsecutive(...\array_values(\array_map(function ($item) {
                return [$item];
            }, BotilkaExtension::AUTOCONFIGURAION_CLASSES_TAG)));

        $container->expects($this->exactly(5))
            ->method('registerForAutoconfiguration')
            ->withConsecutive(...\array_values(\array_map(function ($item) {
                return [$item];
            }, \array_keys(BotilkaExtension::AUTOCONFIGURAION_CLASSES_TAG))))
            ->willReturn($definition);

        $extension = new BotilkaExtension();
        $extension->load([], $container);
    }
}
