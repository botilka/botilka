<?php

namespace Botilka\Bridge\Symfony\Bundle;

use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\CommandActionPass;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\DataProviderPass;
use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\DescriptionContainerPass;
use Botilka\Command\Command;
use Botilka\Command\CommandHandler;
use Botilka\Event\EventHandler;
use Botilka\Query\Query;
use Botilka\Query\QueryHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BotilkaBundle extends Bundle
{
    const AUTOCONFIGURAION_CLASSES_TAG = [
        CommandHandler::class => 'messenger.message_handler',
        QueryHandler::class => 'messenger.message_handler',
        EventHandler::class => 'messenger.message_handler',
        Command::class => 'cqrs.command',
        Query::class => 'cqrs.query',
    ];

    public function build(ContainerBuilder $container)
    {
        foreach (self::AUTOCONFIGURAION_CLASSES_TAG as $className => $tagName) {
            $container->registerForAutoconfiguration($className)
                ->addTag($tagName);
        }

        if ($container->hasExtension('api_platform')) {
            $container->addCompilerPass(new DescriptionContainerPass());
            $container->addCompilerPass(new DataProviderPass());
            $container->addCompilerPass(new CommandActionPass());
        }
    }
}
