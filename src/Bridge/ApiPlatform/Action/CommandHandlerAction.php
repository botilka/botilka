<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Action;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandBus;
use Botilka\Bridge\ApiPlatform\Command\CommandResponseAdapter;
use Botilka\Application\Command\CommandResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CommandHandlerAction
{
    private $commandBus;
    private $validator;

    public function __construct(CommandBus $commandBus, ValidatorInterface $validator)
    {
        $this->commandBus = $commandBus;
        $this->validator = $validator;
    }

    public function __invoke(Command $data): CommandResponseAdapter
    {
        $violations = $this->validator->validate($data);

        if (0 < \count($violations)) {
            throw new ValidationException($violations);
        }

        /** @var CommandResponse $response */
        $response = $this->commandBus->dispatch($data);

        return new CommandResponseAdapter($response);
    }
}
