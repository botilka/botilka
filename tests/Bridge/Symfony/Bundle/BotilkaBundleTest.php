<?php

namespace Botilka\Tests\Bridge\Symfony\Bundle;

use Botilka\Bridge\Symfony\Bundle\BotilkaBundle;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\CommandActionPass;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\DataProviderPass;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\DescriptionContainerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class BotilkaBundleTest extends TestCase
{
    /** @dataProvider buildProvider */
    public function testBuild(bool $hasExtension)
    {
        $container = $this->createMock(ContainerBuilder::class);

        $bundle = new BotilkaBundle();
        $container->expects($this->once())
            ->method('hasExtension')->willReturn($hasExtension);

        $container->expects($hasExtension ? $this->exactly(3) : $this->never())
            ->method('addCompilerPass')
            ->withConsecutive(
                ...[
                    $this->isInstanceOf(DescriptionContainerPass::class),
                    $this->isInstanceOf(DataProviderPass::class),
                    $this->isInstanceOf(CommandActionPass::class),
                ]);

        $bundle->build($container);
    }

    public function buildProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
