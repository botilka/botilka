<?php

namespace Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler;

use Botilka\Bridge\ApiPlatform\Action\CommandAction;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ApiPlatformCommandActionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(CommandAction::class)) {
            return;
        }
        $actionDefinition = $container->getDefinition(CommandAction::class);
        $actionDefinition->setArgument('$descriptionContainer', $container->getDefinition(Command::class.'.description_container'));
    }
}
