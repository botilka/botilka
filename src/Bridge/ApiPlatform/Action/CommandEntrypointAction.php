<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Action;

use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandBus;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final readonly class CommandEntrypointAction
{
    public function __construct(
        private CommandBus $commandBus,
    ) {}

    public function __invoke(Command $data): void
    {
        $this->commandBus->dispatch($data);
    }
}
