<?php

namespace Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler;

use Botilka\Bridge\ApiPlatform\DataProvider\CommandDataProvider;
use Botilka\Bridge\ApiPlatform\DataProvider\QueryDataProvider;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ApiPlatformDataProviderPass implements CompilerPassInterface
{
    private const RESOURCE_TO_DATA_PROVIDER = [
        Command::class => CommandDataProvider::class,
        Query::class => QueryDataProvider::class,
    ];

    public function process(ContainerBuilder $container)
    {
        foreach (self::RESOURCE_TO_DATA_PROVIDER as $resourceClassName => $dataProviderClassName) {
            if (!$container->hasDefinition($dataProviderClassName)) {
                return;
            }
            $dataProviderDefinition = $container->getDefinition($dataProviderClassName);

            $dataProviderDefinition->setArgument('$descriptionContainer', $container->getDefinition($resourceClassName.'.description_container'));
        }
    }
}
