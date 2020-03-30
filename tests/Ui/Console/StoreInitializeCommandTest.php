<?php

declare(strict_types=1);

namespace Botilka\Tests\Ui\Console;

use Botilka\Infrastructure\StoreInitializer;
use Botilka\Tests\Fixtures\Application\EventStore\DummyEventStore;
use Botilka\Ui\Console\StoreInitializeCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class StoreInitializeCommandTest extends TestCase
{
    public function testName(): void
    {
        $command = new StoreInitializeCommand();

        self::assertSame('botilka:store:initialize', $command->getName());
    }

    public function testExecuteNoInitializer(): void
    {
        $command = new StoreInitializeCommand();

        $input = new ArrayInput(['implementation' => 'foo', 'type' => 'bar']);

        $output = new BufferedOutput();

        $command->run($input, $output);
        $stdout = $output->fetch();
        self::assertStringContainsStringIgnoringCase('[WARNING] No initializer found.', $stdout);
    }

    /** @dataProvider executeProvider */
    public function testExecuteSuccess(bool $force): void
    {
        $initializer = $this->createMock(StoreInitializer::class);
        $initializer->expects(self::once())->method('initialize');
        $initializer->expects(self::once())->method('getType')->willReturn('foo');

        $command = new StoreInitializeCommand([$initializer,  new DummyEventStore()]);

        $input = new ArrayInput(['implementation' => 'StoreInitializer', 'type' => 'foo', '--force' => $force]);

        $output = new BufferedOutput();

        $command->run($input, $output);
        $stdout = $output->fetch();
        self::assertStringContainsStringIgnoringCase('Using:', $stdout);
        self::assertStringContainsStringIgnoringCase('[OK] Finished.', $stdout);
    }

    /**
     * @return array<bool[]>
     */
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
        $initializer = $this->createMock(StoreInitializer::class);
        $initializer->expects(self::once())->method('initialize')
            ->willThrowException(new \RuntimeException('Cant\t touch this.'))
        ;
        $initializer->expects(self::once())->method('getType')->willReturn('foo');

        $command = new StoreInitializeCommand([$initializer]);

        $input = new ArrayInput(['implementation' => 'StoreInitializer', 'type' => 'foo', '--force' => $force]);

        $output = new BufferedOutput();

        $command->run($input, $output);
        $stdout = $output->fetch();

        self::assertStringContainsStringIgnoringCase('[ERROR]', $stdout);
    }
}
