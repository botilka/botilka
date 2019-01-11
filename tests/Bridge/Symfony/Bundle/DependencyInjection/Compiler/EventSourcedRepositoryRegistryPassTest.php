<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection\Compiler;

use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\EventSourcedRepositoryRegistryPass;
use Botilka\Repository\EventSourcedRepositoryRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class EventSourcedRepositoryRegistryPassTest extends TestCase
{
    /** @var EventSourcedRepositoryRegistryPass */
    private $compilerPass;

    protected function setUp()
    {
        $this->compilerPass = new EventSourcedRepositoryRegistryPass();
    }

    public function testProcess(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $registryDef = new Definition(EventSourcedRepositoryRegistry::class, ['$repositories']);
        $definitions = [
            new Definition('Foo\\Bar', ['$aggregateRootClassName' => 'Foo\\Bar']),
            new Definition('Foo\\Baz', ['$notCorrespondigArg' => 123]),
        ];

        $container->expects($this->exactly(3))
            ->method('getDefinition')
            ->withConsecutive([EventSourcedRepositoryRegistry::class], ['app.repository.foo_bar'])
            ->willReturnOnConsecutiveCalls($registryDef, ...$definitions);

        $this->assertInstanceOf(CompilerPassInterface::class, $this->compilerPass);

        $servicesIds = [
            'app.repository.foo_bar' => [],
            'app.repository.foo_baz' => [],
        ];

        $container->expects($this->exactly(2))
            ->method('log')
            ->withConsecutive(
                [$this->compilerPass, "Adding to the registry the repository 'app.repository.foo_bar' for 'Foo\\Bar'."],
                [$this->compilerPass, "Skipped: repository 'app.repository.foo_baz' don't have an argument named '\$aggregateRootClassName'."]
            );

        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('botilka.repository.event_sourced')
            ->willReturn($servicesIds);

        $this->compilerPass->process($container);

        $registryRepositoriesArg = $registryDef->getArgument('$repositories');
        $this->assertArrayHasKey('Foo\\Bar', $registryRepositoriesArg);
        $this->assertSame($registryRepositoriesArg['Foo\\Bar'], $definitions[0]);
    }
}
