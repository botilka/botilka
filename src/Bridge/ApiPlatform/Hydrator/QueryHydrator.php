<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Botilka\Application\Query\Query;

/**
 * Provide type hinting & interface implementation.
 */
final class QueryHydrator extends AbstractHydrator implements QueryHydratorInterface
{
    /**
     * @param mixed $data
     */
    public function hydrate($data, string $class): Query
    {
        /** @var Query $query */
        $query = $this->doHydrate($data, $class);

        return $query;
    }
}
