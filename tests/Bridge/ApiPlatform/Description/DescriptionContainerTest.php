<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\Description;

use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Description\DescriptionNotFoundException;
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

    public function testGetNotFound(): void
    {
        $this->expectException(DescriptionNotFoundException::class);
        $this->expectExceptionMessage('Description "bar" was not found. Possible values: "foo".');

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
