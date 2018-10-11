<?php

namespace Botilka\Bridge\Symfony\Bundle;

use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformCommandActionPass;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformDataProviderPass;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformDescriptionContainerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BotilkaBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        if ($container->hasExtension('api_platform')) {
            $container->addCompilerPass(new ApiPlatformDescriptionContainerPass());
            $container->addCompilerPass(new ApiPlatformDataProviderPass());
            $container->addCompilerPass(new ApiPlatformCommandActionPass());
        }
    }
}
