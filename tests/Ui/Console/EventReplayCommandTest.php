<?php

declare(strict_types=1);

namespace Botilka\Tests\Ui\Console;

use Botilka\Event\EventBus;
use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Ui\Console\EventReplayCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class EventReplayCommandTest extends KernelTestCase
{
    /** @var EventStoreManager|MockObject */
    private $eventStoreManager;
    /** @var EventBus|MockObject */
    private $eventBus;
    /** @var array */
    private $events;
    /** @var EventReplayCommand */
    private $command;

    protected function setUp()
    {
        $this->eventStoreManager = $this->createMock(EventStoreManager::class);
        $this->eventBus = $this->createMock(EventBus::class);
        $this->command = new EventReplayCommand($this->eventStoreManager, $this->eventBus);

        $this->events = [
            new ManagedEvent('foo', new StubEvent(42), 0, null, new \DateTimeImmutable(), 'Foo\\Domain'),
            new ManagedEvent('foo', new StubEvent(43), 1, ['foo' => 'bar'], new \DateTimeImmutable(), 'Foo\\Domain'),
        ];
    }

    public function testName(): void
    {
        $this->assertSame('botilka:event_store:replay', $this->command->getName());
    }

    /** @dataProvider executeIdProvider */
    public function testExecuteId(string $value, ?int $from, ?int $to): void
    {
        $this->eventStoreManager->expects($this->once())
            ->method('loadByAggregateRootId')
            ->with($value, $from, $to)
            ->willReturn($this->events);

        $this->eventBus->expects($this->exactly(\count($this->events)))
            ->method('dispatch')
            ->withConsecutive(...$this->events);

        $input = new ArrayInput(['--id' => true, 'value' => $value, '--from' => $from, '--to' => $to]);
        $this->command->run($input, new BufferedOutput());
    }

    public function executeIdProvider(): array
    {
        return [
            ['foo', null, null],
            ['bar', 1, null],
            ['baz', 1, 10],
        ];
    }

    public function testExecuteDomain(): void
    {
        $this->eventStoreManager->expects($this->once())
            ->method('loadByDomain')
            ->with('Foo\\Domain')
            ->willReturn($this->events);

        $this->eventBus->expects($this->exactly(\count($this->events)))
            ->method('dispatch')
            ->withConsecutive(...$this->events);

        $input = new ArrayInput(['--domain' => true, 'value' => 'Foo\\Domain']);
        $this->command->run($input, new BufferedOutput());
    }

    /**
     * @dataProvider executeFailProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage You must set a domain or an id.
     */
    public function testExecuteFail(array $parameters): void
    {
        $input = new ArrayInput($parameters);
        $this->command->run($input, new BufferedOutput());
    }

    public function executeFailProvider(): array
    {
        return [
            [['--id' => true, '--domain' => true, 'value' => 'foo']],
            [['value' => 'foo']],
        ];
    }
}
