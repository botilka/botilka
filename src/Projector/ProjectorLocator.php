<?php

declare(strict_types=1);

namespace Botilka\Projector;

interface ProjectorLocator
{
    /** @return Projector[] */
    public function get(string $eventClass): array;
}
