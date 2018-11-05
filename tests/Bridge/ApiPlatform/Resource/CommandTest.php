<?php

namespace Botilka\Tests\Bridge\ApiPlatform\Resource;

use Botilka\Bridge\ApiPlatform\Resource\Command;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    /** @var Command */
    private $command;

    public function setUp()
    {
        $this->command = new Command('foo_bar', ['foo' => 'baz']);
    }

    public function testGetName()
    {
        $this->assertSame('foo_bar', $this->command->getName());
    }

    public function testGetPayload()
    {
        $this->assertSame(['foo' => 'baz'], $this->command->getPayload());
    }
}
