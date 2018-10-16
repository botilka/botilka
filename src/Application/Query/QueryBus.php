<?php

namespace Botilka\Application\Query;

interface QueryBus
{
    public function dispatch(Query $query);
}
