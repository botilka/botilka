<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine;

use Botilka\Event\Event;
use Botilka\Infrastructure\Doctrine\EventStoreManagerDoctrine;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @internal
 */
#[CoversClass(EventStoreManagerDoctrine::class)]
final class EventStoreManagerDoctrineTest extends TestCase
{
    private DenormalizerInterface&MockObject $denormarlizer;
    private Connection&MockObject $connection;
    private EventStoreManagerDoctrine $manager;

    private string $table = 'foo';

    protected function setUp(): void
    {
        $this->denormarlizer = $this->createMock(DenormalizerInterface::class);
        $this->connection = $this->createMock(Connection::class);

        $this->manager = new EventStoreManagerDoctrine($this->connection, $this->denormarlizer, $this->table);
    }

    #[DataProvider('provideLoadByAggregateRootIdCases')]
    public function testLoadByAggregateRootId(string $id, ?int $from, ?int $to, int $shouldBeCount, string $queryPart, array $parameters): void
    {
        $rows = $this->getRows();

        $expected = \array_slice($rows[$id], $from ?? 0, null !== $to ? $to - $from : null);

        $stmt = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);

        $stmt->expects(self::once())->method('execute')
            ->with($parameters)
            ->willReturn($result)
        ;

        $result->expects(self::once())->method('fetchAllAssociative')
            ->willReturn($expected)
        ;

        $this->denormarlizer->expects(self::exactly(\count($expected)))
            ->method('denormalize')
            ->willReturn($this->createMock(Event::class))
        ;

        $query = trim("SELECT * FROM {$this->table} WHERE id = :id ".$queryPart).' ORDER BY playhead';
        $this->connection->expects(self::once())->method('prepare')
            ->with($query)
            ->willReturn($stmt)
        ;

        $this->manager->loadByAggregateRootId($id, $from, $to);

        self::assertCount($shouldBeCount, $expected);
    }

    public static function provideLoadByAggregateRootIdCases(): iterable
    {
        return [
            ['foo', null, null, 10, '', ['id' => 'foo']],
            ['foo', 4, null, 6, 'AND playhead >= :from', ['id' => 'foo', 'from' => 4]],
            ['foo', 4, 8, 4, 'AND playhead >= :from AND playhead <= :to', ['id' => 'foo', 'from' => 4, 'to' => 8]],
            ['bar', null, null, 5, '', ['id' => 'bar']],
            ['bar', 4, null, 1, 'AND playhead >= :from', ['id' => 'bar', 'from' => 4]],
            ['bar', 3, 8, 2, 'AND playhead >= :from AND playhead <= :to', ['id' => 'bar', 'from' => 3, 'to' => 8]],
        ];
    }

    public function testLoadByDomain(): void
    {
        $rows = [
            [
                'id' => 'foo',
                'playhead' => 42,
                'type' => StubEvent::class,
                'payload' => json_encode(['foo' => 1337]),
                'metadata' => json_encode(null),
                'recorded_on' => (new \DateTimeImmutable('2018-11-14 19:42:51.1234'))->format('Y-m-d H:i:s.u'),
                'domain' => 'Foo\\Domain',
            ],
        ];

        $stmt = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);
        $stmt->expects(self::once())->method('execute')
            ->with(['domain' => 'Foo\\Domain'])
            ->willReturn($result)
        ;
        $result->expects(self::once())->method('fetchAllAssociative')
            ->willReturn($rows)
        ;

        $this->denormarlizer->expects(self::once())
            ->method('denormalize')
            ->willReturn($this->createMock(Event::class))
        ;

        $query = "SELECT * FROM {$this->table} WHERE domain = :domain ORDER BY playhead";
        $this->connection->expects(self::once())->method('prepare')
            ->with($query)
            ->willReturn($stmt)
        ;

        $events = $this->manager->loadByDomain('Foo\\Domain');

        self::assertCount(1, $events);
    }

    #[DataProvider('provideGetCases')]
    public function testGet(string $key, string $method): void
    {
        $rows = [
            [$key => 'foo'],
            [$key => 'bar'],
            [$key => 'baz'],
        ];

        $stmt = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);

        $stmt->expects(self::once())->method('execute')->willReturn($result);
        $result->expects(self::once())->method('fetchAllAssociative')
            ->willReturn($rows)
        ;

        $this->connection->expects(self::once())->method('prepare')
            ->with("SELECT DISTINCT {$key} FROM {$this->table}")
            ->willReturn($stmt)
        ;

        self::assertSame(['foo', 'bar', 'baz'], $this->manager->{$method}());
    }

    public static function provideGetCases(): iterable
    {
        return [
            ['id', 'getAggregateRootIds'],
            ['domain', 'getDomains'],
        ];
    }

    private function getRows(): array
    {
        $rows = ['foo' => [], 'bar' => []];
        foreach (array_keys($rows) as $rowId) {
            for ($i = 0; $i < ('foo' === $rowId ? 10 : 5); ++$i) {
                $rows[$rowId][] = [
                    'id' => 'foo',
                    'playhead' => $i,
                    'type' => StubEvent::class,
                    'payload' => json_encode(['foo' => $i], \JSON_THROW_ON_ERROR),
                    'metadata' => json_encode(null),
                    'recorded_on' => (new \DateTimeImmutable('2018-11-14 19:42:'.($i * 2).'.1234'))->format('Y-m-d H:i:s.u'),
                    'domain' => 'Foo\\Domain',
                ];
            }
        }

        return $rows;
    }
}
