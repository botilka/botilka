<?php

namespace Botilka\Event;

interface EventReplayer
{
    public function replay(string $id, ?int $from = null, ?int $to = null): void;

    public function replayEvents(array $events): void;
}
