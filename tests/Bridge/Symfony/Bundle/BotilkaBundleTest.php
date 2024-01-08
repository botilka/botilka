<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\Symfony\Bundle;

use Botilka\Bridge\Symfony\Bundle\BotilkaBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
#[CoversClass(BotilkaBundle::class)]
final class BotilkaBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $bundle = new BotilkaBundle();

        $bundle->build($container);

        self::assertTrue(true);
    }
}
