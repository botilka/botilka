<?php

declare(strict_types=1);

namespace Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler;

use Botilka\Bridge\ApiPlatform\Action\CommandEntrypointAction;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ApiPlatformCommandEntrypointActionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(CommandEntrypointAction::class)) {
            return;
        }
        $actionDefinition = $container->getDefinition(CommandEntrypointAction::class);
        $actionDefinition->setArgument('$descriptionContainer', $container->getDefinition(Command::class.'.description_container'));
    }
}
