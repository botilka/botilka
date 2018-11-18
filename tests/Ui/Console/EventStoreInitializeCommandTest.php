<?php

declare(strict_types=1);

namespace Botilka\Tests\Ui\Console;

use Botilka\Application\EventStore\EventStoreInitializer;
use Botilka\Tests\Fixtures\Application\EventStore\DummyEventStore;
use Botilka\Ui\Console\EventStoreInitializeCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class EventStoreInitializeCommandTest extends TestCase
{
    public function testName(): void
    {
        $command = new EventStoreInitializeCommand();

        $this->assertSame('botilka:event_store:initialize', $command->getName());
    }

    public function testExecuteNoInitializer(): void
    {
        $command = new EventStoreInitializeCommand();

        $input = new ArrayInput(['implementation' => 'foo']);

        $output = new BufferedOutput();

        $command->run($input, $output);
        $stdout = $output->fetch();
        $this->assertContains('[WARNING] No initializer found.', $stdout);
    }

    /** @dataProvider executeProvider */
    public function testExecuteSuccess(bool $force): void
    {
        $initializer = $this->createMock(EventStoreInitializer::class);
        $initializer->expects($this->once())->method('initialize');

        $command = new EventStoreInitializeCommand([$initializer,  new DummyEventStore()]);

        $input = new ArrayInput(['implementation' => 'EventStoreInitializer', '--force' => $force]);

        $output = new BufferedOutput();

        $command->run($input, $output);
        $stdout = $output->fetch();
        $this->assertContains('Matched:', $stdout);
        $this->assertContains('[OK] Finished.', $stdout);
    }

    public function executeProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /** @dataProvider executeProvider */
    public function testExecuteRuntimeException(bool $force): void
    {
        $initializer = $this->createMock(EventStoreInitializer::class);
        $initializer->expects($this->once())->method('initialize')
            ->willThrowException(new \RuntimeException('Cant\t touch this.'));

        $command = new EventStoreInitializeCommand([$initializer]);

        $input = new ArrayInput(['implementation' => 'event', '--force' => $force]);

        $output = new BufferedOutput();

        $command->run($input, $output);
        $stdout = $output->fetch();

        $this->assertContains('[ERROR]', $stdout);
    }
}
