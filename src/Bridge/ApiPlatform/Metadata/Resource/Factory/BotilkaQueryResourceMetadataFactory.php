<?php

namespace Botilka\Bridge\ApiPlatform\Metadata\Resource\Factory;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Swagger\SwaggerResourcePayloadNormalizerInterface;
use Symfony\Component\HttpFoundation\Request;

final class BotilkaQueryResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    private const SHORT_NAME_TO_EXTEND = 'Query';

    private $decorated;
    private $descriptionContainer;
    private $payloadNormalizer;

    public function __construct(ResourceMetadataFactoryInterface $decorated, DescriptionContainerInterface $descriptionContainer, SwaggerResourcePayloadNormalizerInterface $payloadNormalizer)
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

            $itemOperations = $resourceMetadata->getItemOperations();

            foreach ($this->descriptionContainer as $id => $descritpion) {
                $itemOperations[$id] = [
                    'method' => Request::METHOD_GET,
                    'path' => '/queries/'.$id.'.{_format}',
                    'swagger_context' => [
                        'parameters' => $this->payloadNormalizer->normalize($descritpion['payload']),
                    ],
                ];
            }

            return $resourceMetadata->withItemOperations($itemOperations);
        } catch (ResourceClassNotFoundException $e) {
            return new ResourceMetadata();
        }
    }
}
