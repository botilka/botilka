<?php

declare(strict_types=1);

namespace Botilka\Projector;

interface Projectionist
{
    public function replay(Projection $projection): void;
}
