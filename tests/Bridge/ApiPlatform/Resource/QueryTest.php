<?php

namespace Botilka\Tests\Bridge\ApiPlatform\Resource;

use Botilka\Bridge\ApiPlatform\Resource\Query;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    /** @var Query */
    private $query;

    public function setUp()
    {
        $this->query = new Query('foo_bar', ['foo' => 'baz']);
    }

    public function testGetName()
    {
        $this->assertSame('foo_bar', $this->query->getName());
    }

    public function testGetPayload()
    {
        $this->assertSame(['foo' => 'baz'], $this->query->getPayload());
    }
}
