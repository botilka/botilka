<?php

declare(strict_types=1);

namespace Botilka\Projector;

interface Projectionist
{
    public function dispatch(Projection $projection): void;
}
