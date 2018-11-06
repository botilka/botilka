<?php

namespace Botilka\Bridge\ApiPlatform\Metadata\Resource\Factory;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Symfony\Component\HttpFoundation\Request;

final class BotilkaQueryResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    private const SHORT_NAME_TO_EXTEND = 'Query';

    private $decorated;
    private $descriptionContainer;

    public function __construct(ResourceMetadataFactoryInterface $decorated, DescriptionContainerInterface $descriptionContainer)
    {
        $this->decorated = $decorated;
        $this->descriptionContainer = $descriptionContainer;
    }

    public function create(string $resourceClass): ResourceMetadata
    {
        try {
            $resourceMetadata = $this->decorated->create($resourceClass);

            if (self::SHORT_NAME_TO_EXTEND !== $resourceMetadata->getShortName()) {
                return $resourceMetadata;
            }

            $collectionOperations = $resourceMetadata->getCollectionOperations();

            foreach ($this->descriptionContainer as $id => $descritpion) {
                $collectionOperations[$id] = [
                    'method' => Request::METHOD_GET,
                    'path' => '/queries/'.$id.'.{_format}',
                    'pagination_enabled' => false,
                ];
            }

            return $resourceMetadata->withCollectionOperations($collectionOperations);
        } catch (ResourceClassNotFoundException $e) {
            return new ResourceMetadata();
        }
    }
}
