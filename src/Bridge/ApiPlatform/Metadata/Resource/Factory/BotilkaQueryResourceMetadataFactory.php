<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Metadata\Resource\Factory;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Swagger\SwaggerPayloadNormalizerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BotilkaQueryResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    const SUPPORTED_FORMATS = [
        'jsonld' => ['application/ld+json'],
        'json' => ['application/json'],
        'xml' => ['application/xml', 'text/xml'],
        'yaml' => ['application/x-yaml'],
        'csv' => ['text/csv'],
    ];
    private const SHORT_NAME_TO_EXTEND = 'Query';

    private $decorated;
    private $descriptionContainer;
    private $parameterNormalizer;
    private $formats;
    private $prefix;

    /**
     * @param array<string, array<int, string>> $formats
     */
    public function __construct(ResourceMetadataFactoryInterface $decorated, DescriptionContainerInterface $descriptionContainer, SwaggerPayloadNormalizerInterface $parameterNormalizer, array $formats, ?string $prefix)
    {
        $this->decorated = $decorated;
        $this->descriptionContainer = $descriptionContainer;
        $this->parameterNormalizer = $parameterNormalizer;
        $this->formats = $formats;
        $this->prefix = $prefix;
    }

    public function create(string $resourceClass): ResourceMetadata
    {
        try {
            $resourceMetadata = $this->decorated->create($resourceClass);

            if (self::SHORT_NAME_TO_EXTEND !== $resourceMetadata->getShortName()) {
                return $resourceMetadata;
            }

            $collectionOperations = $resourceMetadata->getCollectionOperations() ?? [];
            $collectionOperations['get']['path'] = '/'.$this->prefix.$collectionOperations['get']['path'];

            $resourceMetadata = $resourceMetadata->withCollectionOperations($collectionOperations);

            $itemOperations = $resourceMetadata->getItemOperations() ?? [];
            $itemOperations['get']['path'] = '/'.$this->prefix.$itemOperations['get']['path'];

            foreach ($this->descriptionContainer as $name => $descritpion) {
                $itemOperations[$name] = [
                    'method' => Request::METHOD_GET,
                    'path' => '/'.$this->prefix.'/queries/'.$name.'.{_format}',
                    'formats' => \array_intersect_key(self::SUPPORTED_FORMATS, $this->formats),
                    'swagger_context' => [
                        'description' => "Execute {$name}",
                        'parameters' => $this->parameterNormalizer->normalize($descritpion['payload']),
                        'responses' => [
                            Response::HTTP_OK => [
                                'description' => "{$name} response",
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                        ],
                                    ],
                                ],
                            ],
                            Response::HTTP_BAD_REQUEST => [
                                'description' => "{$name} error",
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
