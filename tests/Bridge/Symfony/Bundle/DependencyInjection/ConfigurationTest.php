<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection;

use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Configuration;
use Botilka\Infrastructure\InMemory\EventStoreInMemory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    /** @var ConfigurationInterface */
    private $configuration;

    /** @var Processor */
    private $processor;

    protected function setUp()
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
            'doctrine_transaction_middleware' => true,
            'api_platform' => [
                'expose_cq' => true,
                'expose_event_store' => true,
                'endpoint_prefix' => 'cqrs',
            ],
        ];

        self::assertSame($expected, $config);
    }
}
