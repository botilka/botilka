<?php

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
    /** @var EventStoreDoctrine|MockObject */
    private $eventStore;

    public function setUp()
    {
        $this->connection = $this->createMock(Connection::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->eventStore = new EventStoreDoctrine($this->connection, $this->normalizer, $this->denormalizer, 'event_store');
        $this->assertInstanceOf(EventStore::class, $this->eventStore);
    }

    /**
     * @param MockObject|Statement $stmt
     */
    private function addDenormalizerExpectation(MockObject $stmt): void
    {
        $result = [
            ['type' => 'Foo\\Bar', 'payload' => \json_encode(['foo' => 'bar'])],
        ];

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($result);

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(['foo' => 'bar'], 'Foo\\Bar')
            ->willReturn('baz');
    }

    public function testLoad()
    {
        $stmt = $this->createMock(Statement::class);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('SELECT type, payload FROM event_store WHERE id = :id ORDER BY playhead')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['id' => 'foo']);

        $this->addDenormalizerExpectation($stmt);

        $this->assertSame(['baz'], $this->eventStore->load('foo'));
    }

    public function testLoadFromPlayhead()
    {
        $stmt = $this->createMock(Statement::class);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('SELECT type, payload FROM event_store WHERE id = :id AND playhead > :playhead ORDER BY playhead')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['id' => 'foo', 'playhead' => 2]);

        $this->addDenormalizerExpectation($stmt);

        $this->assertSame(['baz'], $this->eventStore->loadFromPlayhead('foo', 2));
    }

    public function testLoadFromPlayheadToPlayhead()
    {
        $stmt = $this->createMock(Statement::class);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('SELECT type, payload FROM event_store WHERE id = :id AND playhead BETWEEN :from AND :to ORDER BY playhead')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['id' => 'foo', 'from' => 2, 'to' => 4]);

        $this->addDenormalizerExpectation($stmt);

        $this->assertSame(['baz'], $this->eventStore->loadFromPlayheadToPlayhead('foo', 2, 4));
    }

    public function testAppend()
    {
        $stmt = $this->createMock(Statement::class);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO event_store VALUES (:id, :playhead, :type, :payload, :metadata, :recordedOn)')
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
            ]);

        $this->eventStore->append('foo', 123, 'Foo\\Bar', $event, ['rab' => 'zab'], $recordedOn);
    }

    /**
     * @expectedException \Botilka\EventStore\EventStoreConcurrencyException
     * @expectedExceptionMessage Duplicate storage of event "Foo\Bar" on aggregate "foo" with playhead 123.
     */
    public function testAppendUniqueConstraintViolationException()
    {
        $stmt = $this->createMock(Statement::class);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new UniqueConstraintViolationException('foo', $this->getMockForAbstractClass(DriverException::class)));

        $this->eventStore->append('foo', 123, 'Foo\\Bar', new StubEvent(123), ['rab' => 'zab'], new \DateTimeImmutable());
    }
}
