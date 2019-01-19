<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine;

use Botilka\EventStore\EventStore;
use Botilka\Infrastructure\Doctrine\EventStoreDoctrine;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\DriverException;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class EventStoreDoctrineTest extends TestCase
{
    /** @var Connection|MockObject */
    private $connection;
    /** @var DenormalizerInterface|MockObject */
    private $denormalizer;
    /** @var NormalizerInterface|MockObject */
    private $normalizer;
    /** @var EventStoreDoctrine */
    private $eventStore;

    protected function setUp()
    {
        $this->connection = $this->createMock(Connection::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->eventStore = new EventStoreDoctrine($this->connection, $this->normalizer, $this->denormalizer, 'event_store');
        $this->assertInstanceOf(EventStore::class, $this->eventStore);
    }

    private function getStatement(bool $withResult): MockObject
    {
        $stmt = $this->createMock(Statement::class);

        $result = $withResult ? [
            ['type' => 'Foo\\Bar', 'payload' => \json_encode(['foo' => 'bar'])],
        ] : [];

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($result);

        return $stmt;
    }

    private function addDenormalizerAssertion(): void
    {
        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(['foo' => 'bar'], 'Foo\\Bar')
            ->willReturn('baz');
    }

    private function addLoadAssertions(string $query, array $executeParameters, bool $withResult): void
    {
        $this->connection->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willReturn($stmt = $this->getStatement($withResult));

        $stmt->expects($this->once())
            ->method('execute')
            ->with($executeParameters);

        if (true === $withResult) {
            $this->addDenormalizerAssertion();
        }
    }

    public function testLoadSuccess(): void
    {
        $this->addLoadAssertions('SELECT type, payload FROM event_store WHERE id = :id ORDER BY playhead', ['id' => 'foo'], true);
        $this->assertEquals(['baz'], $this->eventStore->load('foo'));
    }

    /**
     * @expectedException \Botilka\EventStore\AggregateRootNotFoundException
     * @expectedExceptionMessage No aggregrate root found for foo.
     */
    public function testLoadFail(): void
    {
        $this->addLoadAssertions('SELECT type, payload FROM event_store WHERE id = :id ORDER BY playhead', ['id' => 'foo'], false);
        $this->eventStore->load('foo');
    }

    public function testLoadFromPlayheadSuccess(): void
    {
        $this->addLoadAssertions('SELECT type, payload FROM event_store WHERE id = :id AND playhead >= :from ORDER BY playhead', ['id' => 'foo', 'from' => 2], true);
        $this->assertEquals(['baz'], $this->eventStore->loadFromPlayhead('foo', 2));
    }

    public function testLoadFromPlayheadToPlayheadSuccess(): void
    {
        $this->addLoadAssertions('SELECT type, payload FROM event_store WHERE id = :id AND playhead BETWEEN :from AND :to ORDER BY playhead', ['id' => 'foo', 'from' => 2, 'to' => 4], true);
        $this->assertEquals(['baz'], $this->eventStore->loadFromPlayheadToPlayhead('foo', 2, 4));
    }

    public function testAppend(): void
    {
        $stmt = $this->createMock(Statement::class);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO event_store VALUES (:id, :playhead, :type, :payload, :metadata, :recordedOn, :domain)')
            ->willReturn($stmt);

        $event = new StubEvent(123);
        $this->normalizer->expects($this->once())
            ->method('normalize')
            ->with($event)
            ->willReturn('foo_bar');

        $recordedOn = new \DateTimeImmutable();

        $stmt->expects($this->once())
            ->method('execute')
            ->with([
                'id' => 'foo',
                'playhead' => 123,
                'type' => 'Foo\\Bar',
                'payload' => \json_encode('foo_bar'),
                'metadata' => \json_encode(['rab' => 'zab']),
                'recordedOn' => $recordedOn->format('Y-m-d H:i:s.u'),
                'domain' => 'Foo\\Domain',
            ]);

        $this->eventStore->append('foo', 123, 'Foo\\Bar', $event, ['rab' => 'zab'], $recordedOn, 'Foo\\Domain');
    }

    /**
     * @expectedException \Botilka\EventStore\EventStoreConcurrencyException
     * @expectedExceptionMessage Duplicate storage of event "Foo\Bar" on aggregate "foo" with playhead 123.
     */
    public function testAppendUniqueConstraintViolationException(): void
    {
        $stmt = $this->createMock(Statement::class);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new UniqueConstraintViolationException('foo', $this->getMockForAbstractClass(DriverException::class)));

        $this->eventStore->append('foo', 123, 'Foo\\Bar', new StubEvent(123), ['rab' => 'zab'], new \DateTimeImmutable(), 'Foo\\Domain');
    }
}
