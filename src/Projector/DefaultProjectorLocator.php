<?php

declare(strict_types=1);

namespace Botilka\Projector;

final class DefaultProjectorLocator implements ProjectorLocator
{
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function get(string $eventClass): array
    {
        return $this->data[$eventClass] ?? [];
    }
}
