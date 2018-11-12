<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        foreach ($this->descriptionContainer as $name => $description) {
            $descriptionContainer[] = new Command($name, $description['payload']);
        }

        return $descriptionContainer;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        if (!$this->descriptionContainer->has($id)) {
            throw new NotFoundHttpException(\sprintf('Command "%s" not found.', $id));
        }

        $description = $this->descriptionContainer->get($id);

        return new Command($id, $description['payload']);
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Command::class === $resourceClass;
    }
}
