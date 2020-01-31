<?php

declare(strict_types=1);

namespace Botilka\Tests\Ui\Console;

use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;
use Botilka\Projector\Projectionist;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Ui\Console\ProjectorPlayCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class ProjectorPlayCommandTest extends TestCase
{
    /** @var EventStoreManager|MockObject */
    private $eventStoreManager;
    /** @var Projectionist|MockObject */
    private $projectionist;
    /** @var array */
    private $events;
    /** @var ProjectorPlayCommand */
    private $command;

    protected function setUp()
    {
        $this->eventStoreManager = $this->createMock(EventStoreManager::class);
        $this->projectionist = $this->createMock(Projectionist::class);
        $this->command = new ProjectorPlayCommand($this->eventStoreManager, $this->projectionist);

        $this->events = [
            new ManagedEvent('foo', new StubEvent(42), 0, null, new \DateTimeImmutable(), 'Foo\\Domain'),
            new ManagedEvent('foo', new StubEvent(43), 1, ['foo' => 'bar'], new \DateTimeImmutable(), 'Foo\\Domain'),
        ];
    }

    public function testName(): void
    {
        self::assertSame('botilka:projectors:play', $this->command->getName());
    }

    /** @dataProvider executeIdProvider */
    public function testExecuteId(string $value, ?int $from, ?int $to): void
    {
        $this->eventStoreManager->expects(self::once())
            ->method('loadByAggregateRootId')
            ->with($value, $from, $to)
            ->willReturn($this->events)
        ;

        $this->projectionist->expects(self::exactly(\count($this->events)))
            ->method('play')
            ->withConsecutive(...$this->events)
        ;

        $input = new ArrayInput(['--id' => true, 'value' => $value, '--from' => $from, '--to' => $to]);
        $output = new BufferedOutput();
        $this->command->run($input, $output);
        $stdout = $output->fetch();
        self::assertContains('(     0): Botilka\Tests\Fixtures\Domain\StubEvent (null)', $stdout);
        self::assertContains('(     1): Botilka\Tests\Fixtures\Domain\StubEvent ({"foo":"bar"})', $stdout);
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
        $this->eventStoreManager->expects(self::once())
            ->method('loadByDomain')
            ->with('Foo\\Domain')
            ->willReturn($this->events)
        ;

        $this->projectionist->expects(self::exactly(\count($this->events)))
            ->method('play')
            ->withConsecutive(...$this->events)
        ;

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
