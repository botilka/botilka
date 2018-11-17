<?php

declare(strict_types=1);

namespace Botilka\Tests\Ui\Console;

use Botilka\EventStore\EventStoreManager;
use Botilka\EventStore\ManagedEvent;
use Botilka\Projector\Projectionist;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Ui\Console\ProjectorReplayCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class ProjectorReplayCommandTest extends TestCase
{
    /** @dataProvider executeProvider */
    public function testExecute(string $value, ?int $from, ?int $to): void
    {
        $manager = $this->createMock(EventStoreManager::class);
        $projectionist = $this->createMock(Projectionist::class);
        $command = new ProjectorReplayCommand($manager, $projectionist);

        $this->assertSame('botilka:projector:replay', $command->getName());

        $events = [
            new ManagedEvent('foo', new StubEvent(42), 0, null, new \DateTimeImmutable(), 'Foo\\Domain'),
            new ManagedEvent('foo', new StubEvent(43), 1, null, new \DateTimeImmutable(), 'Foo\\Domain'),
        ];

        $manager->expects($this->once())
            ->method('loadByAggregateRootId')
            ->with($value, $from, $to)
            ->willReturn($events);

        $input = new ArrayInput(['target' => 'id', 'value' => $value, '--from' => $from, '--to' => $to]);
        $output = new BufferedOutput();
        $command->run($input, $output);
        $stdout = $output->fetch();
        $this->assertContains('[NOTE] 2 events found.', $stdout);
    }

    public function executeProvider(): array
    {
        return [
            ['foo', null, null],
            ['bar', 1, null],
            ['baz', 1, 10],
        ];
    }
}
