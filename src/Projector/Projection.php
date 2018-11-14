<?php

declare(strict_types=1);

namespace Botilka\Projector;

use Botilka\Event\Event;

interface Projection
{
    public function getEvent(): Event;
}
