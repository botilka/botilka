<?php

declare(strict_types=1);

namespace Botilka\Application\Query;

interface QueryBus
{
    /**
     * @return mixed
     */
    public function dispatch(Query $query);
}
