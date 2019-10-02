<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\Symfony\Bundle;

use Botilka\Bridge\Symfony\Bundle\BotilkaBundle;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformCommandEntrypointActionPass;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformDataProviderPass;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformDescriptionContainerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class BotilkaBundleTest extends TestCase
{
    /** @dataProvider buildProvider */
    public function testBuild(bool $hasExtension): void
    {
        $container = $this->createMock(ContainerBuilder::class);

        $bundle = new BotilkaBundle();
        $container->expects(self::once())
            ->method('hasExtension')->willReturn($hasExtension);

        $container->expects($hasExtension ? self::exactly(3) : self::never())
            ->method('addCompilerPass')
            ->withConsecutive(
                [self::isInstanceOf(ApiPlatformDescriptionContainerPass::class)],
                [self::isInstanceOf(ApiPlatformDataProviderPass::class)],
                [self::isInstanceOf(ApiPlatformCommandEntrypointActionPass::class)]
            )
        ;

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
