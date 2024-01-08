<?php

declare(strict_types=1);

namespace Botilka\Application\Query;

interface QueryBus
{
    public function dispatch(Query $query): mixed;
}
