<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\Command;

use Botilka\Application\Command\CommandResponse;
use Botilka\Bridge\ApiPlatform\Command\CommandResponseAdapter;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;

final class CommandResponseAdapterTest extends TestCase
{
    public function testGetId(): void
    {
        $event = new StubEvent(123);
        $commandResponse = new CommandResponse('foo', new StubEvent(123));
        $adapter = new CommandResponseAdapter($commandResponse);
        self::assertSame('foo', $adapter->getId());
    }
}
