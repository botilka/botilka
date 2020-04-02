<?php

declare(strict_types=1);

namespace Botilka\Tests\Repository;

use Botilka\Repository\EventSourcedRepository;
use Botilka\Repository\EventSourcedRepositoryRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class EventSourcedRepositoryRegistryTest extends TestCase
{
    /** @var EventSourcedRepositoryRegistry */
    private $registry;
    /** @var array<string, EventSourcedRepository|MockObject> */
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

    /**
     * @return array<int, array<string|bool>>
     */
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

    public function testGetFail(): void
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('You have requested a non-existent service "Event sourced repository for aggregate root \'DateTime\' not found.');

        $this->registry->get(\DateTime::class);
    }
}
