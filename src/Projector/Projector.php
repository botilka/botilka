<?php

declare(strict_types=1);

namespace Botilka\Projector;

interface Projector
{
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents();
}
