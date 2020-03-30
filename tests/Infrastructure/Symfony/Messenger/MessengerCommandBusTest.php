<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Symfony\Messenger;

use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandResponse;
use Botilka\Infrastructure\Symfony\Messenger\MessengerCommandBus;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class MessengerCommandBusTest extends TestCase
{
    public function testDispatchHandled(): void
    {
        $command = new SimpleCommand('foo', 132);
        $commandResponse = new CommandResponse('foo', new StubEvent(123));
        $stamp = new HandledStamp($commandResponse, 'foo');

        $bus = $this->getMessengerCommandBus($command, $commandResponse, $stamp);

        $result = $bus->dispatch($command);
        self::assertSame($commandResponse, $result);
        self::assertSame($commandResponse->getId(), $result->getId());
    }

    public function testDispatchSent(): void
    {
        $command = new SimpleCommand('foo', 132);
        $commandResponse = null;
        $stamp = new SentStamp(\get_class($this), 'this');

        $bus = $this->getMessengerCommandBus($command, null, $stamp);

        $result = $bus->dispatch($command);
        self::assertNull($result);
    }

    /** @dataProvider dispatchLogicExceptionProvider */
    public function testDispatchLogicException(StampInterface ...$stamps): void
    {
        $command = new SimpleCommand('foo', 132);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Message of type "Botilka\Tests\Fixtures\Application\Command\SimpleCommand" was handled 0 or too many times, or was not sent.');

        $bus = $this->getMessengerCommandBus($command, null, ...$stamps);

        $result = $bus->dispatch($command);
    }

    /**
     * @return array<string, array<int, StampInterface>>
     */
    public function dispatchLogicExceptionProvider(): array
    {
        return [
            'not handled or sent' => [new class() implements StampInterface {
            }],
            'too many handlers' => [new HandledStamp('FooFoo', 'foo'), new HandledStamp('BarBar', 'bar')],
        ];
    }

    private function getMessengerCommandBus(Command $command, ?CommandResponse $commandResponse, StampInterface ...$stamps): MessengerCommandBus
    {
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::once())
            ->method('dispatch')
            ->with($command)
            ->willReturn(new Envelope($command, \array_values($stamps)))
        ;

        return new MessengerCommandBus($messageBus);
    }
}
