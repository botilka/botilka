<?php

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Botilka\Application\Query\Query;
use Doctrine\ORM\Internal\Hydration\HydrationException;

interface QueryHydratorInterface
{
    /**
     * @throws HydrationException
     */
    public function hydrate($data, string $class): Query;
}
