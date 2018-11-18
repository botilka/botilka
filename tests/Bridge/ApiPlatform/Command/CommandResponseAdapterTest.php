<?php

declare(strict_types=1);

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
        $commandResponse = new CommandResponse('foo', 123, $event, 'Foo\\Domain');
        $adapter = new CommandResponseAdapter($commandResponse);
        $this->assertSame('foo', $adapter->getId());
    }
}
