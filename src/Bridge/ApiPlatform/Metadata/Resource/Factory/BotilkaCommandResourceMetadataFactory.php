<?php

namespace Botilka\Bridge\ApiPlatform\Metadata\Resource\Factory;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use Botilka\Bridge\ApiPlatform\Action\CommandHandlerAction;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Swagger\SwaggerPayloadNormalizerInterface;
use Symfony\Component\HttpFoundation\Request;

final class BotilkaCommandResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    private const SHORT_NAME_TO_EXTEND = 'Command';

    private $decorated;
    private $descriptionContainer;
    private $payloadNormalizer;

    public function __construct(ResourceMetadataFactoryInterface $decorated, DescriptionContainerInterface $descriptionContainer, SwaggerPayloadNormalizerInterface $payloadNormalizer)
    {
        $this->decorated = $decorated;
        $this->descriptionContainer = $descriptionContainer;
        $this->payloadNormalizer = $payloadNormalizer;
    }

    public function create(string $resourceClass): ResourceMetadata
    {
        try {
            $resourceMetadata = $this->decorated->create($resourceClass);

            if (self::SHORT_NAME_TO_EXTEND !== $resourceMetadata->getShortName()) {
                return $resourceMetadata;
            }

            $collectionOperations = $resourceMetadata->getCollectionOperations() ?? [];

            foreach ($this->descriptionContainer as $id => $descritpion) {
                $collectionOperations[$id] = [
                    'controller' => CommandHandlerAction::class,
                    'method' => Request::METHOD_POST,
                    'path' => '/commands/'.$id.'.{_format}',
                    'swagger_context' => [
                        'consumes' => 'application/json',
                        'parameters' => [
                            [
                                'in' => 'body',
                                'schema' => $this->payloadNormalizer->normalize($descritpion['payload']),
                            ],
                        ],
                    ],
                ];
            }

            return $resourceMetadata->withCollectionOperations($collectionOperations);
        } catch (ResourceClassNotFoundException $e) {
            return new ResourceMetadata();
        }
    }
}
