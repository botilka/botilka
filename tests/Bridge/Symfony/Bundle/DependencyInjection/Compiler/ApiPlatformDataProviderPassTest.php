<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection\Compiler;

use Botilka\Bridge\ApiPlatform\DataProvider\CommandDataProvider;
use Botilka\Bridge\ApiPlatform\DataProvider\QueryDataProvider;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformDataProviderPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class ApiPlatformDataProviderPassTest extends TestCase
{
    /** @var ApiPlatformDataProviderPass */
    private $compilerPass;

    protected function setUp(): void
    {
        $this->compilerPass = new ApiPlatformDataProviderPass();
    }

    public function testProcess(): void
    {
        $container = $this->createMock(ContainerBuilder::class);

        self::assertInstanceOf(CompilerPassInterface::class, $this->compilerPass);

        $container->expects(self::exactly(2))
            ->method('hasDefinition')
            ->withConsecutive([CommandDataProvider::class], [QueryDataProvider::class])
            ->willReturn(true)
        ;

        $commandDataProviderDefinition = $this->createMock(Definition::class);
        $queryDataProviderDefinition = $this->createMock(Definition::class);

        $container->expects(self::exactly(4))
            ->method('getDefinition')
            ->withConsecutive(
                [CommandDataProvider::class],
                [Command::class.'.description_container'],
                [QueryDataProvider::class],
                [Query::class.'.description_container']
            )
            ->willReturnOnConsecutiveCalls($commandDataProviderDefinition, 'foo', $queryDataProviderDefinition, 'bar')
        ;

        // command
        $commandDataProviderDefinition->expects(self::once())
            ->method('setArgument')
            ->with('$descriptionContainer', 'foo')
        ;

        $commandDataProviderDefinition->expects(self::exactly(2))
            ->method('addTag')
            ->withConsecutive(['api_platform.collection_data_provider'], ['api_platform.item_data_provider'])
            ->willReturn($commandDataProviderDefinition)
        ;

        // query
        $queryDataProviderDefinition->expects(self::once())
            ->method('setArgument')
            ->with('$descriptionContainer', 'bar')
        ;

        $queryDataProviderDefinition->expects(self::exactly(2))
            ->method('addTag')
            ->withConsecutive(['api_platform.collection_data_provider'], ['api_platform.item_data_provider'])
            ->willReturn($queryDataProviderDefinition)
        ;

        $this->compilerPass->process($container);
    }

    public function testNoProcess(): void
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects(self::exactly(2))
            ->method('hasDefinition')
            ->withConsecutive([CommandDataProvider::class], [QueryDataProvider::class])
            ->willReturn(false)
        ;

        $container->expects(self::never())
            ->method('getDefinition')
            ->withConsecutive([CommandDataProvider::class], [QueryDataProvider::class])
        ;

        $this->compilerPass->process($container);
    }
}
