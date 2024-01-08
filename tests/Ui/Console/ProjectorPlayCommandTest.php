<?php

declare(strict_types=1);

namespace Botilka\Tests\Ui\Console;

use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;
use Botilka\Projector\Projection;
use Botilka\Projector\Projectionist;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Ui\Console\ProjectorPlayCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @internal
 */
#[CoversClass(ProjectorPlayCommand::class)]
final class ProjectorPlayCommandTest extends TestCase
{
    private EventStoreManager&MockObject $eventStoreManager;
    private MockObject&Projectionist $projectionist;
    /** @var ManagedEvent[] */
    private array $events;
    private ProjectorPlayCommand $command;

    protected function setUp(): void
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

    #[DataProvider('provideExecuteIdCases')]
    public function testExecuteId(string $value, ?int $from, ?int $to): void
    {
        $this->eventStoreManager->expects(self::once())
            ->method('loadByAggregateRootId')
            ->with($value, $from, $to)
            ->willReturn($this->events)
        ;

        $this->projectionist->expects(self::exactly(\count($this->events)))
            ->method('play')
            ->with(self::isInstanceOf(Projection::class))
        ;

        $input = new ArrayInput(['--id' => true, 'value' => $value, '--from' => $from, '--to' => $to]);
        $output = new BufferedOutput();
        $this->command->run($input, $output);
        $stdout = $output->fetch();
        self::assertStringContainsStringIgnoringCase('(     0): Botilka\Tests\Fixtures\Domain\StubEvent (null)', $stdout);
        self::assertStringContainsStringIgnoringCase('(     1): Botilka\Tests\Fixtures\Domain\StubEvent ({"foo":"bar"})', $stdout);
    }

    /**
     * @return array<int, array<int, int|string|null>>
     */
    public static function provideExecuteIdCases(): iterable
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
            ->with(self::isInstanceOf(Projection::class))
        ;

        $input = new ArrayInput(['--domain' => true, 'value' => 'Foo\\Domain']);
        $this->command->run($input, new BufferedOutput());
    }

    /**
     * @param array<int, array<string, string|true>> $parameters
     */
    #[DataProvider('provideExecuteFailCases')]
    public function testExecuteFail(array $parameters): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You must set a domain or an id.');

        $input = new ArrayInput($parameters);
        $this->command->run($input, new BufferedOutput());
    }

    /**
     * @return array<int, array<int, array<string, string|true>>>
     */
    public static function provideExecuteFailCases(): iterable
    {
        return [
            [['--id' => true, '--domain' => true, 'value' => 'foo']],
            [['value' => 'foo']],
        ];
    }
}
