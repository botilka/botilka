<?php

namespace Botilka\Tests\Bridge\ApiPlatform\Command;

use Botilka\Bridge\ApiPlatform\Command\CommandResponseAdapter;
use Botilka\Application\Command\CommandResponse;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;

final class CommandResponseAdapterTest extends TestCase
{
    public function testGetId(): void
    {
        $event = new StubEvent(123);
        $commandResponse = new CommandResponse('foo', 123, $event);
        $adapter = new CommandResponseAdapter($commandResponse);
        $this->assertSame('foo', $adapter->getId());
    }
}
