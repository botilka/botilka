<?php

namespace Botilka\Bridge\ApiPlatform\Metadata\Resource\Factory;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Swagger\SwaggerQueryParameterNormalizerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BotilkaQueryResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    private const SHORT_NAME_TO_EXTEND = 'Query';

    private $decorated;
    private $descriptionContainer;
    private $payloadNormalizer;

    public function __construct(ResourceMetadataFactoryInterface $decorated, DescriptionContainerInterface $descriptionContainer, SwaggerQueryParameterNormalizerInterface $payloadNormalizer)
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

            foreach ($this->descriptionContainer as $name => $descritpion) {
                $itemOperations[$name] = [
                    'method' => Request::METHOD_GET,
                    'path' => '/queries/'.$name.'.{_format}',
                    'swagger_context' => [
                        'description' => "Execute $name",
                        'parameters' => $this->payloadNormalizer->normalize($descritpion['payload']),
                        'responses' => [
                            Response::HTTP_OK => [
                                'description' => "$name response",
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                        ],
                                    ],
                                ],
                            ],
                            Response::HTTP_BAD_REQUEST => [
                                'description' => "$name error",
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];
            }

            return $resourceMetadata->withItemOperations($itemOperations);
        } catch (ResourceClassNotFoundException $e) {
            return new ResourceMetadata();
        }
    }
}
