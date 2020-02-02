<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\DataProvider;

use Botilka\Bridge\ApiPlatform\DataProvider\CommandDataProvider;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CommandDataProviderTest extends TestCase
{
    /** @var CommandDataProvider */
    private $dataProvider;

    protected function setUp(): void
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

    public function testGetItemNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Command "non-existent" not found.');

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

    /**
     * @return array<int, array<int, bool|class-string>>
     */
    public function supportsDataProvider(): array
    {
        return [
            [true, Command::class],
            [false, \DateTime::class],
        ];
    }
}
