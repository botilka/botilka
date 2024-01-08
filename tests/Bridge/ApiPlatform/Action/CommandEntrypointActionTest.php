<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\Action;

use Botilka\Application\Command\CommandBus;
use Botilka\Bridge\ApiPlatform\Action\CommandEntrypointAction;
use Botilka\Tests\Fixtures\Application\Command\SimpleCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CommandEntrypointAction::class)]
final class CommandEntrypointActionTest extends TestCase
{
    public function testInvoke(): void
    {
        $commandBus = $this->createMock(CommandBus::class);

        $command = new SimpleCommand('foo', 3210);

        $commandBus->expects(self::once())
            ->method('dispatch')
            ->with($command)
        ;

        $action = new CommandEntrypointAction($commandBus);

        $action($command);
    }
}
