<?php

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Botilka\Application\Command\Command;

interface CommandHydratorInterface
{
    public function hydrate($data, string $class): Command;
}
