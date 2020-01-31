<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\Description;

use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use PHPUnit\Framework\TestCase;

final class DescriptionContainerTest extends TestCase
{
    /** @var DescriptionContainer */
    private $descriptionContainer;

    protected function setUp(): void
    {
        $this->descriptionContainer = new DescriptionContainer(['foo' => [
            'class' => 'Foo\\Bar',
            'payload' => ['some' => 'string'], ],
        ]);
    }

    public function testGetFound(): void
    {
        self::assertSame(['class' => 'Foo\\Bar', 'payload' => ['some' => 'string']], $this->descriptionContainer->get('foo'));
    }

    /**
     * @expectedException \Botilka\Bridge\ApiPlatform\Description\DescriptionNotFoundException
     * @expectedExceptionMessage Description "bar" was not found. Possible values: "foo".
     */
    public function testGetNotFound(): void
    {
        $this->descriptionContainer->get('bar');
    }

    /** @dataProvider hasProvider */
    public function testHas(bool $expected, string $name): void
    {
        self::assertSame($expected, $this->descriptionContainer->has($name));
    }

    public function hasProvider(): array
    {
        return [
            [false, 'bar'],
            [true, 'foo'],
        ];
    }
}
