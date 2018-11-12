<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Botilka\Application\Command\Command;

/**
 * Provide type hinting & interface implementation.
 */
final class CommandHydrator extends AbstractHydrator implements CommandHydratorInterface
{
    public function hydrate($data, string $class): Command
    {
        return $this->doHydrate($data, $class);
    }
}
