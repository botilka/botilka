<?php

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Botilka\Application\Query\Query;

interface QueryHydratorInterface
{
    /**
     * @throws HydrationException
     */
    public function hydrate($data, string $class): Query;
}
