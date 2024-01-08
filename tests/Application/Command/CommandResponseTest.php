<?php

declare(strict_types=1);

namespace Botilka\Tests\Application\Command;

use Botilka\Application\Command\CommandResponse;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CommandResponse::class)]
final class CommandResponseTest extends TestCase
{
    private CommandResponse $commandResponse;
    private StubEvent $event;

    protected function setUp(): void
    {
        $this->event = new StubEvent(123);
        $this->commandResponse = new CommandResponse('foo', $this->event);
    }

    public function testGetEvent(): void
    {
        self::assertSame($this->event, $this->commandResponse->getEvent());
    }

    public function testGetId(): void
    {
        self::assertSame('foo', $this->commandResponse->getId());
    }

    public function testFromValues(): void
    {
        $commandResponse = CommandResponse::fromValues('bar', $this->event);
        self::assertInstanceOf(CommandResponse::class, $commandResponse);
        self::assertSame('bar', $commandResponse->getId());
        self::assertSame($this->event, $commandResponse->getEvent());
    }
}
