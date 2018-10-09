<?php

namespace Botilka\Tests\Bridge\ApiPlatform\DataProvider;

use Botilka\Bridge\ApiPlatform\DataProvider\CommandDataProvider;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use PHPUnit\Framework\TestCase;

final class CommandDataProviderTest extends TestCase
{
    public function testGetCollection()
    {
        $descriptionContainer = new DescriptionContainer(['foo' => ['type' => 'foo', 'payload' => ['foo' => 'bar']]]);
        $dataProvider = new CommandDataProvider($descriptionContainer);
        $collection = $dataProvider->getCollection('whatever');
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Command::class, $collection[0]);
    }

    public function testGetItem()
    {
        $descriptionContainer = new DescriptionContainer(['foo' => ['type' => 'foo', 'payload' => ['foo' => 'bar']]]);
        $dataProvider = new CommandDataProvider($descriptionContainer);
        $item = $dataProvider->getItem('whatever', 'foo');
        $this->assertInstanceOf(Command::class, $item);
    }

    /** @dataProvider supportsDataProvider */
    public function testSupports(bool $expected, string $resourceClass)
    {
        $dataProvider = new CommandDataProvider(new DescriptionContainer([]));
        $this->assertSame($expected, $dataProvider->supports($resourceClass));
    }

    public function supportsDataProvider()
    {
        return [
            [true, Command::class],
            [false, \DateTime::class],
        ];
    }
}
