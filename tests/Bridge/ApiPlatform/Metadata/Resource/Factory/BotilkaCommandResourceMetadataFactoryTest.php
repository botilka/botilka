<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\Metadata\Resource\Factory;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Metadata\Resource\Factory\BotilkaCommandResourceMetadataFactory;
use Botilka\Bridge\ApiPlatform\Swagger\SwaggerPayloadNormalizerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BotilkaCommandResourceMetadataFactoryTest extends TestCase
{
    /** @var BotilkaCommandResourceMetadataFactory */
    private $factory;
    /** @var ResourceMetadataFactoryInterface|MockObject */
    private $decorated;
    /** @var DescriptionContainerInterface|MockObject */
    private $descriptionContainer;
    /** @var SwaggerPayloadNormalizerInterface|MockObject */
    private $payloadNormalizer;

    protected function setUp()
    {
        $this->decorated = $this->createMock(ResourceMetadataFactoryInterface::class);
        $this->descriptionContainer = $this->createMock(DescriptionContainerInterface::class);
        $this->payloadNormalizer = $this->createMock(SwaggerPayloadNormalizerInterface::class);
        $this->factory = new BotilkaCommandResourceMetadataFactory($this->decorated, $this->descriptionContainer, $this->payloadNormalizer, 'bazprefix');
    }

    public function testCreateResourceClassNotFoundException(): void
    {
        $this->decorated->expects($this->once())
            ->method('create')
            ->willThrowException(new ResourceClassNotFoundException());

        $metadata = $this->factory->create('Foo\\Bar');
        $this->assertNull($metadata->getShortName());
    }

    public function testCreateNotExtending(): void
    {
        $metadata = new ResourceMetadata('NotCommand');
        $this->decorated->expects($this->once())
            ->method('create')
            ->with('Foo\\Bar')
            ->willReturn($metadata);

        $this->descriptionContainer->expects($this->never())
            ->method('getIterator');

        $this->assertSame($metadata, $this->factory->create('Foo\\Bar'));
    }

    public function testCreate(): void
    {
        $itemOperations = [
            'get' => [
                'path' => '/description/commands/{id}',
                'input' => null,
                'output' => null,
                'method' => 'GET',
            ],
        ];

        $collectionOperations = [
            'get' => [
                'path' => '/description/commands',
                'pagination_enabled' => false,
                'input' => null,
                'output' => null,
                'method' => 'GET',
            ],
        ];

        $this->decorated->expects($this->once())
            ->method('create')
            ->with('Foo\\Bar')
            ->willReturn(new ResourceMetadata('Command', null, null, $itemOperations, $collectionOperations));

        $this->descriptionContainer->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                'foo' => [
                    'class' => 'Foo\\Bar',
                    'payload' => ['some' => 'string'],
                ],
            ]));

        $this->payloadNormalizer->expects($this->once())
            ->method('normalize')
            ->with(['some' => 'string'])
            ->willReturn(['foo_body']);

        $resourceMetadata = $this->factory->create('Foo\\Bar');

        $itemOperations['get']['path'] = '/bazprefix'.$itemOperations['get']['path'];
        $collectionOperations['get']['path'] = '/bazprefix'.$collectionOperations['get']['path'];
        $this->assertSame($collectionOperations + [
            'foo' => [
                'method' => Request::METHOD_POST,
                'path' => '/bazprefix/commands/foo.{_format}',
                'swagger_context' => [
                    'description' => 'Execute foo',
                    'parameters' => [
                        [
                            'in' => 'body',
                            'schema' => ['foo_body'],
                        ],
                    ],
                    'responses' => [
                        Response::HTTP_OK => [
                            'description' => 'foo response',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                    ],
                                ],
                            ],
                        ],
                        Response::HTTP_BAD_REQUEST => [
                            'description' => 'foo error',
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
            ],
        ], $resourceMetadata->getCollectionOperations());
        $this->assertSame($itemOperations, $resourceMetadata->getItemOperations());
    }
}
