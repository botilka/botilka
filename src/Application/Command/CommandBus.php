<?php

namespace Botilka\Application\Command;

interface CommandBus
{
    public function dispatch(Command $command): CommandResponse;
}
