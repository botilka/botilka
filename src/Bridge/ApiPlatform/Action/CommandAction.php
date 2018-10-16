<?php

namespace Botilka\Bridge\ApiPlatform\Action;

use Botilka\Bridge\ApiPlatform\Command\CommandResponseAdapter;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Application\Command\CommandResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Botilka\Application\Command\Command as CQRSCommand;

final class CommandAction
{
    private $commandBus;
    private $serializer;
    private $descriptionContainer;

    public function __construct(MessageBusInterface $commandBus, SerializerInterface $serializer, DescriptionContainerInterface $descriptionContainer)
    {
        $this->commandBus = $commandBus;
        $this->serializer = $serializer;
        $this->descriptionContainer = $descriptionContainer;
    }

    public function __invoke(Command $data)
    {
        $description = $this->descriptionContainer->get($data->getId());

        /** @var CQRSCommand $command */
        $command = $this->serializer->deserialize(\json_encode($data->getPayload()), $description['class'], 'json');

        /** @var CommandResponse $response */
        $response = $this->commandBus->dispatch($command);

        return new CommandResponseAdapter($response);
    }
}
