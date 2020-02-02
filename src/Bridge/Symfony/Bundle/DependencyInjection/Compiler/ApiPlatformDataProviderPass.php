<?php

declare(strict_types=1);

namespace Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler;

use Botilka\Bridge\ApiPlatform\DataProvider\CommandDataProvider;
use Botilka\Bridge\ApiPlatform\DataProvider\QueryDataProvider;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ApiPlatformDataProviderPass implements CompilerPassInterface
{
    private const RESOURCE_TO_DATA_PROVIDER = [
        Command::class => CommandDataProvider::class,
        Query::class => QueryDataProvider::class,
    ];

    public function process(ContainerBuilder $container): void
    {
        foreach (self::RESOURCE_TO_DATA_PROVIDER as $resourceClassName => $dataProviderClassName) {
            if (!$container->hasDefinition($dataProviderClassName)) {
                continue;
            }
            $dataProviderDefinition = $container->getDefinition($dataProviderClassName)
                ->addTag('api_platform.collection_data_provider')
                ->addTag('api_platform.item_data_provider')
            ;

            $dataProviderDefinition->setArgument('$descriptionContainer', $container->getDefinition($resourceClassName.'.description_container'));
        }
    }
}
