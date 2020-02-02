<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Botilka\Application\Query\Query;

interface QueryHydratorInterface
{
    /**
     * @param mixed $data
     */
    public function hydrate($data, string $class): Query;
}
