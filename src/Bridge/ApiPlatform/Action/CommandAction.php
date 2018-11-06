<?php

namespace Botilka\Bridge\ApiPlatform\Action;

use Botilka\Application\Command\CommandBus;
use Botilka\Bridge\ApiPlatform\Command\CommandResponseAdapter;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Application\Command\CommandResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Botilka\Application\Command\Command as CQRSCommand;

/**
 * @todo rename
 */
final class CommandAction
{
    private $commandBus;
    private $serializer;
    private $descriptionContainer;

    public function __construct(CommandBus $commandBus, SerializerInterface $serializer, DescriptionContainerInterface $descriptionContainer)
    {
        $this->commandBus = $commandBus;
        $this->serializer = $serializer;
        $this->descriptionContainer = $descriptionContainer;
    }

    public function __invoke(Command $data): CommandResponseAdapter
    {
        $description = $this->descriptionContainer->get($data->getName());

        /** @var CQRSCommand $command */
        $command = $this->serializer->deserialize(\json_encode($data->getPayload()), $description['class'], 'json');

        /** @var CommandResponse $response */
        $response = $this->commandBus->dispatch($command);

        return new CommandResponseAdapter($response);
    }
}
