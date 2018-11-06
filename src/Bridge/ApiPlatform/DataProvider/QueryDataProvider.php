<?php

namespace Botilka\Bridge\ApiPlatform\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Botilka\Bridge\ApiPlatform\Description\DescriptionNotFoundException;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class QueryDataProvider implements CollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $descriptionContainer;

    public function __construct(DescriptionContainerInterface $descriptionContainer)
    {
        $this->descriptionContainer = $descriptionContainer;
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        $collection = [];
        foreach ($this->descriptionContainer as $id => $description) {
            $collection[] = new Query($id, $description['payload']);
        }

        return $collection;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        try {
            $description = $this->descriptionContainer->get($id);
        } catch (DescriptionNotFoundException $e) {
            throw new NotFoundHttpException(\sprintf('Query "%s" not found.', $id));
        }

        return new Query($id, $description['payload']);
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Query::class === $resourceClass;
    }
}
