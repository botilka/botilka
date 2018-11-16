<?php

declare(strict_types=1);

namespace Botilka\Event;

interface EventReplayer
{
    public function replay(string $id, ?int $from = null, ?int $to = null): void;
}
