<?php

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Botilka\Application\Query\Query;

/**
 * Provide type hinting & interface implementation.
 */
final class QueryHydrator extends AbstractHydrator implements QueryHydratorInterface
{
    public function hydrate($data, string $class): Query
    {
        return $this->doHydrate($data, $class);
    }
}
