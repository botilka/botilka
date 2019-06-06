<?php

declare(strict_types=1);

namespace Botilka\Application\Command;

interface CommandBus
{
    /**
     * @throws \LogicException if message was not or too many times handled or not sent
     */
    public function dispatch(Command $command): ?CommandResponse;
}
