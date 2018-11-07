<?php

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Botilka\Application\Command\Command;

interface CommandHydratorInterface
{
    /**
     * @throws HydrationException
     */
    public function hydrate($data, string $class): Command;
}
