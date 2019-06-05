<?php

declare(strict_types=1);

namespace Botilka\Application\Command;

interface CommandBus
{
    public function dispatch(Command $command): ?CommandResponse;
}
