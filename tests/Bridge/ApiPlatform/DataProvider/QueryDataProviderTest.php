<?php

namespace Botilka\Tests\Bridge\ApiPlatform\DataProvider;

use Botilka\Bridge\ApiPlatform\DataProvider\QueryDataProvider;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use PHPUnit\Framework\TestCase;

final class QueryDataProviderTest extends TestCase
{
    /** @var QueryDataProvider */
    private $dataProvider;

    public function setUp()
    {
        $descriptionContainer = new DescriptionContainer(['foo_query' => [
            'class' => 'Foo\\BarQuery',
            'payload' => ['some' => 'string'],
        ]]);

        $this->dataProvider = new QueryDataProvider($descriptionContainer);
    }

    public function testGetItem()
    {
        $item = $this->dataProvider->getItem('whatever', 'foo_query');

        $this->assertInstanceOf(Query::class, $item);
        $this->assertSame('foo_query', $item->getName());
        $this->assertSame(['some' => 'string'], $item->getPayload());
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Query "non-existent" not found.
     */
    public function testGetItemNotFoundException()
    {
        $this->dataProvider->getItem('whatever', 'non-existent');
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
