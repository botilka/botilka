<?php

declare(strict_types=1);

namespace Botilka\Tests\Repository;

use Botilka\Repository\DefaultEventSourcedRepositoryRegistry;
use Botilka\Repository\EventSourcedRepository;
use Botilka\Repository\EventSourcedRepositoryRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * @internal
 */
#[CoversClass(EventSourcedRepositoryRegistry::class)]
final class EventSourcedRepositoryRegistryTest extends TestCase
{
    private EventSourcedRepositoryRegistry $registry;
    /** @var array<class-string, MockObject&EventSourcedRepository> */
    private array $repositories;

    protected function setUp(): void
    {
        $this->repositories = [
            'Foo\\Bar' => $this->createMock(EventSourcedRepository::class),
        ];
        $this->registry = new DefaultEventSourcedRepositoryRegistry($this->repositories);
    }

    #[DataProvider('provideHasCases')]
    public function testHas(string $className, bool $expected): void
    {
        self::assertSame($expected, $this->registry->has($className));
    }

    /**
     * @return array<int, array{class-string, bool}>
     */
    public static function provideHasCases(): iterable
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
