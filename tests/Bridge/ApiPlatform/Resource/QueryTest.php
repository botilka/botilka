<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\Resource;

use Botilka\Bridge\ApiPlatform\Resource\Query;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    /** @var Query */
    private $query;

    protected function setUp()
    {
        $this->query = new Query('foo_bar', ['foo' => 'baz']);
    }

    public function testGetName(): void
    {
        $this->assertSame('foo_bar', $this->query->getName());
    }

    public function testGetPayload(): void
    {
        $this->assertSame(['foo' => 'baz'], $this->query->getPayload());
    }
}
