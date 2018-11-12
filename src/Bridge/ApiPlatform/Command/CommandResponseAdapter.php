<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Command;

use Botilka\Application\Command\CommandResponse;

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
