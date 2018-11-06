<?php

namespace Botilka\Bridge\ApiPlatform\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Resource\Command;

/**
 * @see DescriptionContainerPass
 */
final class CommandDataProvider implements CollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $descriptionContainer;

    public function __construct(DescriptionContainerInterface $descriptionContainer)
    {
        $this->descriptionContainer = $descriptionContainer;
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        $descriptionContainer = [];
        foreach ($this->descriptionContainer as $id => $description) {
            $descriptionContainer[] = new Command($id, $description['payload']);
        }

        return $descriptionContainer;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        $itemDescription = $this->descriptionContainer->get($id);

        return new Command($id, $itemDescription['payload']);
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Command::class === $resourceClass;
    }
}
