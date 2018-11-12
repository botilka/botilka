<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Action;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Botilka\Application\Command\CommandBus;
use Botilka\Bridge\ApiPlatform\Command\CommandResponseAdapter;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Hydrator\CommandHydratorInterface;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CommandEntrypointAction
{
    private $commandBus;
    private $descriptionContainer;
    private $hydrator;

    public function __construct(CommandBus $commandBus, DescriptionContainerInterface $descriptionContainer, CommandHydratorInterface $hydrator)
    {
        $this->commandBus = $commandBus;
        $this->descriptionContainer = $descriptionContainer;
        $this->hydrator = $hydrator;
    }

    public function __invoke(Command $data): CommandResponseAdapter
    {
        $name = $data->getName();

        if (!$this->descriptionContainer->has($name)) {
            throw new NotFoundHttpException("Command '$name' not found.");
        }

        $description = $this->descriptionContainer->get($name);

        try {
            $command = $this->hydrator->hydrate($data->getPayload(), $description['class']);
        } catch (ValidationException $e) {
            throw new BadRequestHttpException($e->__toString());
        }

        $response = $this->commandBus->dispatch($command);

        return new CommandResponseAdapter($response);
    }
}
