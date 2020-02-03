<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @see DescriptionContainerPass
 */
final class QueryDataProvider implements CollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $descriptionContainer;

    public function __construct(DescriptionContainerInterface $descriptionContainer)
    {
        $this->descriptionContainer = $descriptionContainer;
    }

    /**
     * @return Query[]
     */
    public function getCollection(string $resourceClass, string $operationName = null): array
    {
        $collection = [];
        foreach ($this->descriptionContainer as $name => $description) {
            $collection[] = new Query($name, $description['payload']);
        }

        return $collection;
    }

    /**
     * @param string $id
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): Query
    {
        if (!$this->descriptionContainer->has($id)) {
            throw new NotFoundHttpException(\sprintf('Query "%s" not found.', $id));
        }

        $description = $this->descriptionContainer->get($id);

        return new Query($id, $description['payload']);
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Query::class === $resourceClass;
    }
}
