<?php

namespace Botilka\Tests\Bridge\ApiPlatform\DataProvider;

use Botilka\Bridge\ApiPlatform\DataProvider\QueryDataProvider;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\SerializerInterface;

final class QueryDataProviderTest extends TestCase
{
    /** @var MockObject|MessageBusInterface */
    private $queryBus;
    /** @var MockObject|SerializerInterface */
    private $serializer;
    /** @var Request */
    private $request;
    /** @var QueryDataProvider */
    private $dataProvider;

    public function setUp()
    {
        $queryBus = $this->createMock(MessageBusInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $descriptionContainer = new DescriptionContainer(['foo_query' => [
            'class' => 'Foo\\BarQuery',
            'payload' => ['some' => 'string'],
        ]]);

        $queryResource = new Query('foo_query', ['foo' => 'string']);

        $request = new Request(['foo' => 'bar'], [], ['data' => $queryResource]);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $this->queryBus = $queryBus;
        $this->serializer = $serializer;
        $this->request = $request;
        $this->dataProvider = new QueryDataProvider($queryBus, $serializer, $descriptionContainer, $requestStack);
    }

    public function testGetItem()
    {
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(\json_encode($this->request->query->all()), 'Foo\\BarQuery', 'json')
            ->willReturn('foo');

        $this->queryBus->expects($this->once())
            ->method('dispatch')
            ->with('foo')
            ->willReturn('bar_response');

        $this->assertSame('bar_response', $this->dataProvider->getItem('whatever', 'foo_query'));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Query "non-existent" not found.
     */
    public function testGetItemNotFoundException()
    {
        $this->dataProvider->getItem('whatever', 'non-existent');
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage Unable to create "foo_query" query. Please check your parameters.
     */
    public function testGetItemBadRequestHttpException()
    {
        $this->serializer->method('deserialize')->willThrowException(new MissingConstructorArgumentsException());
        $this->dataProvider->getItem('whatever', 'foo_query');
    }

    public function testGetCollection()
    {
        $collection = $this->dataProvider->getCollection('whatever');
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Query::class, $collection[0]);
    }

    /** @dataProvider supportsDataProvider */
    public function testSupports(bool $expected, string $resourceClass)
    {
        $this->assertSame($expected, $this->dataProvider->supports($resourceClass));
    }

    public function supportsDataProvider()
    {
        return [
            [true, Query::class],
            [false, \DateTime::class],
        ];
    }
}
