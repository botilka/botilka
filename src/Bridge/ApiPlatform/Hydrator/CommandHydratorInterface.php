<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Botilka\Application\Command\Command;

interface CommandHydratorInterface
{
    /**
     * @param mixed $data
     */
    public function hydrate($data, string $class): Command;
}
