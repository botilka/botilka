<?php

declare(strict_types=1);

namespace Botilka\Tests\Repository;

use Botilka\Repository\EventSourcedRepository;
use Botilka\Repository\EventSourcedRepositoryRegistry;
use PHPUnit\Framework\TestCase;

final class EventSourcedRepositoryRegistryTest extends TestCase
{
    /** @var EventSourcedRepositoryRegistry */
    private $registry;
    /** @var array */
    private $repositories;

    protected function setUp(): void
    {
        $this->repositories = [
            'Foo\\Bar' => $this->createMock(EventSourcedRepository::class),
        ];
        $this->registry = new EventSourcedRepositoryRegistry($this->repositories);
    }

    /** @dataProvider hasProvider */
    public function testHas(string $className, bool $expected): void
    {
        self::assertSame($expected, $this->registry->has($className));
    }

    public function hasProvider(): array
    {
        return [
            ['Foo\\Bar', true],
            [\DateTime::class, false],
        ];
    }

    public function testGetSuccess(): void
    {
        self::assertSame($this->repositories['Foo\\Bar'], $this->registry->get('Foo\\Bar'));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage You have requested a non-existent service "Event sourced repository for aggregate root 'DateTime' not found.
     */
    public function testGetFail(): void
    {
        $this->registry->get(\DateTime::class);
    }
}
