<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\DataProvider;

use Botilka\Bridge\ApiPlatform\DataProvider\CommandDataProvider;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use PHPUnit\Framework\TestCase;

final class CommandDataProviderTest extends TestCase
{
    /** @var CommandDataProvider */
    private $dataProvider;

    protected function setUp()
    {
        $descriptionContainer = new DescriptionContainer(['foo' => [
            'class' => 'Foo\\Bar',
            'payload' => ['some' => 'string'],
        ]]);

        $this->dataProvider = new CommandDataProvider($descriptionContainer);
    }

    public function testGetItem(): void
    {
        $item = $this->dataProvider->getItem('whatever', 'foo');

        self::assertInstanceOf(Command::class, $item);
        self::assertSame('foo', $item->getName());
        self::assertSame(['some' => 'string'], $item->getPayload());
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Command "non-existent" not found.
     */
    public function testGetItemNotFoundException(): void
    {
        $this->dataProvider->getItem('whatever', 'non-existent');
    }

    public function testGetCollection(): void
    {
        /** @var array $collection */
        $collection = $this->dataProvider->getCollection('whatever');
        self::assertCount(1, $collection);
        self::assertInstanceOf(Command::class, $collection[0]);
    }

    /** @dataProvider supportsDataProvider */
    public function testSupports(bool $expected, string $resourceClass): void
    {
        self::assertSame($expected, $this->dataProvider->supports($resourceClass));
    }

    public function supportsDataProvider()
    {
        return [
            [true, Command::class],
            [false, \DateTime::class],
        ];
    }
}
