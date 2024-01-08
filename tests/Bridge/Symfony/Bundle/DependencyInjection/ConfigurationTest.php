<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection;

use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Configuration;
use Botilka\Infrastructure\InMemory\EventStoreInMemory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @internal
 */
#[CoversClass(Configuration::class)]
final class ConfigurationTest extends TestCase
{
    private Configuration $configuration;

    private Processor $processor;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testGetConfigTreeBuilder(): void
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();
        $config = $this->processor->processConfiguration($this->configuration, []);

        self::assertInstanceOf(ConfigurationInterface::class, $this->configuration);
        self::assertInstanceOf(TreeBuilder::class, $treeBuilder);

        $expected = [
            'event_store' => EventStoreInMemory::class,
            'default_messenger_config' => true,
        ];

        self::assertSame($expected, $config);
    }
}
