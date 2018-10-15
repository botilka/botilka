<?php

namespace Botilka\Tests\Bridge\Symfony\Bundle;

use Botilka\Bridge\Symfony\Bundle\BotilkaBundle;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformCommandActionPass;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformDataProviderPass;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformDescriptionContainerPass;
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
                [$this->isInstanceOf(ApiPlatformDescriptionContainerPass::class)],
                [$this->isInstanceOf(ApiPlatformDataProviderPass::class)],
                [$this->isInstanceOf(ApiPlatformCommandActionPass::class)]
            );

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
