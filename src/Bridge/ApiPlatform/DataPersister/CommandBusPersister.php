<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\DataPersister;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use Botilka\Application\Command\Command;
use Botilka\Application\Command\CommandBus;
use Botilka\Application\Command\CommandResponse;
use Botilka\Bridge\ApiPlatform\Command\CommandResponseAdapter;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CommandBusPersister implements DataPersisterInterface
{
    private $commandBus;
    private $validator;

    public function __construct(CommandBus $commandBus, ValidatorInterface $validator)
    {
        $this->commandBus = $commandBus;
        $this->validator = $validator;
    }

    public function supports($data): bool
    {
        return $data instanceof Command;
    }

    /**
     * @param mixed $data
     *
     * @return CommandResponseAdapter
     */
    public function persist($data)
    {
        $violations = $this->validator->validate($data);

        if (0 < \count($violations)) {
            throw new ValidationException($violations);
        }

        /** @var CommandResponse $response */
        $response = $this->commandBus->dispatch($data);

        return new CommandResponseAdapter($response);
    }

    /**
     * @param mixed $data
     *
     * @throws \LogicException must not be called in an event-sourced application
     */
    public function remove($data): void
    {
        throw new \LogicException('Remove must not be called in an event-sourced application.');
    }
}
