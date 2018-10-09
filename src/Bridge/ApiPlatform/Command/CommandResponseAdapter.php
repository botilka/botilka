<?php

namespace Botilka\Bridge\ApiPlatform\Command;

use Botilka\Command\CommandResponse;

final class CommandResponseAdapter
{
    private $commandResponse;

    public function __construct(CommandResponse $commandResponse)
    {
        $this->commandResponse = $commandResponse;
    }

    public function getId(): string
    {
        return $this->commandResponse->getId();
    }
}
