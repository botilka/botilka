<?php

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection\Compiler;

use Botilka\Bridge\ApiPlatform\Action\CommandAction;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformCommandActionPass;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ApiPlatformCommandActionPassTest extends TestCase
{
    /** @var ApiPlatformCommandActionPass */
    private $compilerPass;

    public function setUp()
    {
        $this->compilerPass = new ApiPlatformCommandActionPass();
    }

    public function testProcess()
    {
        $container = $this->createMock(ContainerBuilder::class);

        $this->assertInstanceOf(CompilerPassInterface::class, $this->compilerPass);

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with(CommandAction::class)
            ->willReturn(true);

        $commandActionDefinition = $this->createMock(Definition::class);

        $container->expects($this->exactly(2))
            ->method('getDefinition')
            ->withConsecutive([CommandAction::class], [Command::class.'.description_container'])
            ->willReturnOnConsecutiveCalls($commandActionDefinition, 'foo');

        $commandActionDefinition->expects($this->once())
            ->method('setArgument')
            ->with('$descriptionContainer', 'foo');

        $this->compilerPass->process($container);
    }

    public function testNoProcess()
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with(CommandAction::class)
            ->willReturn(false);

        $container->expects($this->never())->method('getDefinition')->with(CommandAction::class);

        $this->compilerPass->process($container);
    }
}
