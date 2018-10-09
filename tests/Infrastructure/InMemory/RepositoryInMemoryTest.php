<?php

namespace Botilka\Tests\Infrastructure\InMemory;

use Botilka\Infrastructure\InMemory\RepositoryInMemory;
use Botilka\Tests\Domain\StubAggregateRoot;
use PHPUnit\Framework\TestCase;

final class RepositoryInMemoryTest extends TestCase
{
    /** @var RepositoryInMemory */
    private $repository;

    public function setUp()
    {
        $this->repository = new RepositoryInMemory();
    }

    public function testDelete()
    {
        $aggretate = new StubAggregateRoot('foo');
        $this->repository->add($aggretate);
        $this->repository->delete($aggretate->getAggregateRootId());
        $this->assertNull($this->repository->get('foo'));
    }

    public function testAll()
    {
        $aggretateFoo = new StubAggregateRoot('foo');
        $aggretateBar = new StubAggregateRoot('bar');
        $this->repository->add($aggretateFoo);
        $this->repository->add($aggretateBar);

        $expected = [
            'foo' => $aggretateFoo,
            'bar' => $aggretateBar,
        ];

        $this->assertSame($expected, $this->repository->all());
    }

    public function testGet()
    {
        $aggretate = new StubAggregateRoot('foo');
        $this->repository->add($aggretate);
        $this->assertSame($aggretate, $this->repository->get('foo'));
    }

    public function testSave()
    {
        $aggretate = new StubAggregateRoot('foo');
        $this->repository->save($aggretate);
        $this->assertSame($aggretate, $this->repository->get('foo'));
    }
}
