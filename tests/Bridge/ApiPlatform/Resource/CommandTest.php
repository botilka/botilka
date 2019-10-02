<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\Resource;

use Botilka\Bridge\ApiPlatform\Resource\Command;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    /** @var Command */
    private $command;

    protected function setUp()
    {
        $this->command = new Command('foo_bar', ['foo' => 'baz']);
    }

    public function testGetName(): void
    {
        self::assertSame('foo_bar', $this->command->getName());
    }

    public function testGetPayload(): void
    {
        self::assertSame(['foo' => 'baz'], $this->command->getPayload());
    }
}
