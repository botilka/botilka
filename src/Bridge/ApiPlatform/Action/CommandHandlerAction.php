<?php

namespace Botilka\Bridge\ApiPlatform\Action;

use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandBus;
use Botilka\Bridge\ApiPlatform\Command\CommandResponseAdapter;
use Botilka\Application\Command\CommandResponse;

/**
 * @todo rename
 */
final class CommandHandlerAction
{
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(Command $data): CommandResponseAdapter
    {
        /** @var CommandResponse $response */
        $response = $this->commandBus->dispatch($data);

        return new CommandResponseAdapter($response);
    }
}
