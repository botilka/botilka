<?php

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Botilka\Application\Query\Query;

interface QueryHydratorInterface
{
    public function hydrate($data, string $class): Query;
}
