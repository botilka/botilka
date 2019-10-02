<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection\Compiler;

use Botilka\Bridge\ApiPlatform\Action\CommandEntrypointAction;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformCommandEntrypointActionPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class ApiPlatformCommandEntrypointActionPassTest extends TestCase
{
    /** @var ApiPlatformCommandEntrypointActionPass */
    private $compilerPass;

    protected function setUp()
    {
        $this->compilerPass = new ApiPlatformCommandEntrypointActionPass();
    }

    public function testProcess(): void
    {
        $container = $this->createMock(ContainerBuilder::class);

        self::assertInstanceOf(CompilerPassInterface::class, $this->compilerPass);

        $container->expects(self::once())
            ->method('hasDefinition')
            ->with(CommandEntrypointAction::class)
            ->willReturn(true)
        ;

        $commandActionDefinition = $this->createMock(Definition::class);

        $container->expects(self::exactly(2))
            ->method('getDefinition')
            ->withConsecutive([CommandEntrypointAction::class], [Command::class.'.description_container'])
            ->willReturnOnConsecutiveCalls($commandActionDefinition, 'foo')
        ;

        $commandActionDefinition->expects(self::once())
            ->method('setArgument')
            ->with('$descriptionContainer', 'foo')
        ;

        $this->compilerPass->process($container);
    }

    public function testNoProcess(): void
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects(self::once())
            ->method('hasDefinition')
            ->with(CommandEntrypointAction::class)
            ->willReturn(false)
        ;

        $container->expects(self::never())->method('getDefinition')->with(CommandEntrypointAction::class);

        $this->compilerPass->process($container);
    }
}
