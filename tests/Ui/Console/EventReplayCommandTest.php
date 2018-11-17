<?php

declare(strict_types=1);

namespace Botilka\Tests\Ui\Console;

use Botilka\Event\EventBus;
use Botilka\EventStore\EventStoreManager;
use Botilka\Ui\Console\EventReplayCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class EventReplayCommandTest extends KernelTestCase
{
    /** @dataProvider executeProvider */
    public function testExecute(string $value, ?int $from, ?int $to): void
    {
        $eventStoreManager = $this->createMock(EventStoreManager::class);
        $eventBus = $this->createMock(EventBus::class);
        $command = new EventReplayCommand($eventStoreManager, $eventBus);

        $this->assertSame('botilka:event_store:replay', $command->getName());

        $input = new ArrayInput(['target' => 'id', 'value' => $value, '--from' => $from, '--to' => $to]);
        $command->run($input, new BufferedOutput());
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
