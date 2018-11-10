<?php

namespace Botilka\Bridge\ApiPlatform\Metadata\Resource\Factory;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use Botilka\Bridge\ApiPlatform\Action\CommandHandlerAction;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Swagger\SwaggerPayloadNormalizerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

            foreach ($this->descriptionContainer as $name => $descritpion) {
                $collectionOperations[$name] = [
                    'controller' => CommandHandlerAction::class,
                    'method' => Request::METHOD_POST,
                    'path' => '/commands/'.$name.'.{_format}',
                    'swagger_context' => [
                        'description' => "Execute $name",
                        'parameters' => [
                            [
                                'in' => 'body',
                                'schema' => $this->payloadNormalizer->normalize($descritpion['payload']),
                            ],
                        ],
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

            return $resourceMetadata->withCollectionOperations($collectionOperations);
        } catch (ResourceClassNotFoundException $e) {
            return new ResourceMetadata();
        }
    }
}
