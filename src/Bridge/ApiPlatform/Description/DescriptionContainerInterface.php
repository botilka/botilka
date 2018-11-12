<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Description;

interface DescriptionContainerInterface extends \IteratorAggregate
{
    public function has(string $name): bool;

    /**
     * @throws DescriptionNotFoundException
     */
    public function get(string $name): array;
}
