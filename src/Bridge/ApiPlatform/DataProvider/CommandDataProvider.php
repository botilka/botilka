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

    /**
     * @return Command[]
     */
    public function getCollection(string $resourceClass, string $operationName = null): array
    {
        $descriptionContainer = [];
        foreach ($this->descriptionContainer as $name => $description) {
            $descriptionContainer[] = new Command($name, $description['payload']);
        }

        return $descriptionContainer;
    }

    /**
     * @param string               $id
     * @param array<string, mixed> $context
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): Command
    {
        if (!$this->descriptionContainer->has($id)) {
            throw new NotFoundHttpException(\sprintf('Command "%s" not found.', $id));
        }

        $description = $this->descriptionContainer->get($id);

        return new Command($id, $description['payload']);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Command::class === $resourceClass;
    }
}
