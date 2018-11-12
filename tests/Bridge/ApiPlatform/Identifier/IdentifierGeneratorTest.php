<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\Identifier;

use Botilka\Bridge\ApiPlatform\Identifier\IdentifierGenerator;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use PHPUnit\Framework\TestCase;

final class IdentifierGeneratorTest extends TestCase
{
    /** @dataProvider generateProvider */
    public function testGenerate(string $expected, string $type, string $className): void
    {
        $this->assertSame($expected, IdentifierGenerator::generate($type, $className));
    }

    public function generateProvider(): array
    {
        return [
            ['foo_do_something', 'Foo\\DoSomethingCommand', Command::class],
            ['bar_get_some_other_thing', 'Bar\\GetSomeOtherThingQuery', Query::class],
        ];
    }
}
