<?php

declare(strict_types=1);

namespace Botilka\Tests\Application\Command;

use Botilka\Application\Command\CommandResponse;
use Botilka\Event\Event;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;

final class CommandResponseTest extends TestCase
{
    /** @var CommandResponse */
    private $commandResponse;
    /** @var Event */
    private $event;

    protected function setUp()
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
