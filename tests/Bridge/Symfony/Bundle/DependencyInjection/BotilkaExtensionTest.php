<?php

namespace Botilka\Tests\Bridge\Symfony\Bundle\DependencyInjection;

use ApiPlatform\Core\Bridge\Symfony\Bundle\DependencyInjection\ApiPlatformExtension;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\BotilkaExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BotilkaExtensionTest extends TestCase
{
    public function testPrependWithApiPlatform()
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new ApiPlatformExtension());
        $extension = new BotilkaExtension();
        $extension->prepend($container);

        $this->assertSame([
            [
                'mapping' => [
                    'paths' => [
                        '%kernel.project_dir%/src/Botilka/Bridge/ApiPlatform/Resource',
                        '%kernel.project_dir%/src/Botilka/Infrastructure/Doctrine',
                    ],
                ],
            ],
        ], $container->getExtensionConfig('api_platform'));
    }

    public function testPrependWithoutApiPlatform()
    {
        $container = new ContainerBuilder();
        $extension = new BotilkaExtension();
        $extension->prepend($container);

        $this->assertSame([], $container->getExtensionConfig('api_platform'));
    }
}
