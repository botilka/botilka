<?php

namespace Botilka\Tests\Ui\Console;

use Botilka\Event\EventReplayerInterface;
use Botilka\Ui\Console\BotilkaReplayCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class BotilkaReplayCommandTest extends KernelTestCase
{
    /** @dataProvider executeProvider */
    public function testExecute(string $id, ?int $from, ?int $to)
    {
        $replayer = $this->createMock(EventReplayerInterface::class);
        $command = new BotilkaReplayCommand($replayer);

        $replayer->expects($this->once())
            ->method('replay')
            ->with(...[$id, $from, $to]);

        $input = new ArrayInput(['id' => $id, '--from' => $from, '--to' => $to]);
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
