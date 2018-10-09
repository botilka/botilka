<?php

namespace Botilka\Tests\Bridge\ApiPlatform\Description;

use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use PHPUnit\Framework\TestCase;

final class DescriptionContainerTest extends TestCase
{
    /** @var DescriptionContainer */
    private $descriptionContainer;

    public function setUp()
    {
        $this->descriptionContainer = new DescriptionContainer(['foo' => ['class' => 'Foo\\Bar', 'payload' => ['bar' => 'baz']]]);
    }

    public function testAll()
    {
        $this->assertSame(['foo' => ['class' => 'Foo\\Bar', 'payload' => ['bar' => 'baz']]], $this->descriptionContainer->all());
    }

    public function testGetFound()
    {
        $this->assertSame(['class' => 'Foo\\Bar', 'payload' => ['bar' => 'baz']], $this->descriptionContainer->get('foo'));
    }

    /**
     * @expectedException \Botilka\Bridge\ApiPlatform\Description\DescriptionNotFoundException
     * @expectedExceptionMessage Description "bar" was not found. Possible values: "foo".
     */
    public function testGetNotFound()
    {
        $this->descriptionContainer->get('bar');
    }
}
