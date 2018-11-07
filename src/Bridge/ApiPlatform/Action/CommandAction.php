<?php

namespace Botilka\Bridge\ApiPlatform\Action;

use Botilka\Application\Command\CommandBus;
use Botilka\Bridge\ApiPlatform\Command\CommandResponseAdapter;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Application\Command\CommandResponse;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Botilka\Application\Command\Command as CQRSCommand;

/**
 * @todo rename
 */
final class CommandAction
{
    private $commandBus;
    private $denormalizer;
    private $descriptionContainer;

    public function __construct(CommandBus $commandBus, DenormalizerInterface $denormalizer, DescriptionContainerInterface $descriptionContainer)
    {
        $this->commandBus = $commandBus;
        $this->serializer = $denormalizer;
        $this->descriptionContainer = $descriptionContainer;
    }

    public function __invoke(Command $data): CommandResponseAdapter
    {
        $description = $this->descriptionContainer->get($data->getName());

        /** @var CQRSCommand $command */
        $command = $this->serializer->denormalize($data->getPayload(), $description['class']);

        /** @var CommandResponse $response */
        $response = $this->commandBus->dispatch($command);

        return new CommandResponseAdapter($response);
    }
}
