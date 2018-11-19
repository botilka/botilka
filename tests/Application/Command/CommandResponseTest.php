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
        $this->assertSame($this->event, $this->commandResponse->getEvent());
    }

    public function testGetId(): void
    {
        $this->assertSame('foo', $this->commandResponse->getId());
    }

    public function testFromValues(): void
    {
        $commandResponse = CommandResponse::fromValues('bar', $this->event);
        $this->assertInstanceOf(CommandResponse::class, $commandResponse);
        $this->assertSame('bar', $commandResponse->getId());
        $this->assertSame($this->event, $commandResponse->getEvent());
    }
}
