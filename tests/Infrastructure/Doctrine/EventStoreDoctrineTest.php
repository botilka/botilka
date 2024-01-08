<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine;

use Botilka\EventStore\AggregateRootNotFoundException;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Botilka\Infrastructure\Doctrine\EventStoreDoctrine;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
#[CoversClass(EventStoreDoctrine::class)]
final class EventStoreDoctrineTest extends TestCase
{
    private Connection&MockObject $connection;
    private DenormalizerInterface&MockObject $denormalizer;
    private MockObject&NormalizerInterface $normalizer;
    private EventStoreDoctrine $eventStore;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->eventStore = new EventStoreDoctrine($this->connection, $this->normalizer, $this->denormalizer, 'event_store');
        self::assertInstanceOf(EventStore::class, $this->eventStore);
    }

    public function testLoadSuccess(): void
    {
        $this->addLoadAssertions('SELECT type, payload FROM event_store WHERE id = :id ORDER BY playhead', ['id' => 'foo'], true);
        $this->eventStore->load('foo');
    }

    public function testLoadFail(): void
    {
        $this->expectException(AggregateRootNotFoundException::class);
        $this->expectExceptionMessage('No aggregrate root found for foo.');

        $this->addLoadAssertions('SELECT type, payload FROM event_store WHERE id = :id ORDER BY playhead', ['id' => 'foo'], false);
        $this->eventStore->load('foo');
    }

    public function testLoadFromPlayheadSuccess(): void
    {
        $this->addLoadAssertions('SELECT type, payload FROM event_store WHERE id = :id AND playhead >= :from ORDER BY playhead', ['id' => 'foo', 'from' => 2], true);
        $this->eventStore->loadFromPlayhead('foo', 2);
    }

    public function testLoadFromPlayheadToPlayheadSuccess(): void
    {
        $this->addLoadAssertions('SELECT type, payload FROM event_store WHERE id = :id AND playhead BETWEEN :from AND :to ORDER BY playhead', ['id' => 'foo', 'from' => 2, 'to' => 4], true);
        $this->eventStore->loadFromPlayheadToPlayhead('foo', 2, 4);
    }

    public function testAppend(): void
    {
        $stmt = $this->createMock(Statement::class);

        $this->connection->expects(self::once())
            ->method('prepare')
            ->with('INSERT INTO event_store VALUES (:id, :playhead, :type, :payload, :metadata, :recordedOn, :domain)')
            ->willReturn($stmt)
        ;

        $event = new StubEvent(123);
        $this->normalizer->expects(self::once())
            ->method('normalize')
            ->with($event)
            ->willReturn('foo_bar')
        ;

        $recordedOn = new \DateTimeImmutable();

        $stmt->expects(self::once())
            ->method('execute')
            ->with([
                'id' => 'foo',
                'playhead' => 123,
                'type' => 'Foo\\Bar',
                'payload' => json_encode('foo_bar'),
                'metadata' => json_encode(['rab' => 'zab']),
                'recordedOn' => $recordedOn->format('Y-m-d H:i:s.u'),
                'domain' => 'Foo\\Domain',
            ])
        ;

        $this->eventStore->append('foo', 123, 'Foo\\Bar', $event, ['rab' => 'zab'], $recordedOn, 'Foo\\Domain');
    }

    public function testAppendUniqueConstraintViolationException(): void
    {
        $stmt = $this->createMock(Statement::class);

        $this->connection->expects(self::once())
            ->method('prepare')
            ->willReturn($stmt)
        ;

        $stmt->expects(self::once())
            ->method('execute')
            ->willThrowException($this->createMock(UniqueConstraintViolationException::class))
        ;

        $this->expectException(EventStoreConcurrencyException::class);
        $this->expectExceptionMessage('Duplicate storage of event "Foo\Bar" on aggregate "foo" with playhead 123.');

        $this->eventStore->append('foo', 123, 'Foo\\Bar', new StubEvent(123), ['rab' => 'zab'], new \DateTimeImmutable(), 'Foo\\Domain');
    }

    private function getStatement(bool $withResult): MockObject&Statement
    {
        $stmt = $this->createMock(Statement::class);
        $result = $this->createMock(Result::class);

        $result->expects(self::once())->method('fetchAllAssociative')->willReturn(
            $withResult ? [
                ['type' => 'Foo\\Bar', 'payload' => json_encode(['foo' => 'bar'])],
            ] : []
        );

        $stmt->expects(self::once())
            ->method('execute')
            ->willReturn($result)
        ;

        return $stmt;
    }

    private function addDenormalizerAssertion(): void
    {
        $this->denormalizer->expects(self::once())
            ->method('denormalize')
            ->with(['foo' => 'bar'], 'Foo\\Bar')
        ;
    }

    private function addLoadAssertions(string $query, array $executeParameters, bool $withResult): void
    {
        $stmt = $this->getStatement($withResult);
        $this->connection->expects(self::once())
            ->method('prepare')
            ->with($query)
            ->willReturn($stmt)
        ;

        $stmt->expects(self::once())
            ->method('execute')
            ->with($executeParameters)
        ;

        if ($withResult) {
            $this->addDenormalizerAssertion();
        }
    }
}
