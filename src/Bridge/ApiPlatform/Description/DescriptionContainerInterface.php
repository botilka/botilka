<?php

namespace Botilka\Bridge\ApiPlatform\Description;

interface DescriptionContainerInterface extends \IteratorAggregate, \Countable
{
    public function has(string $id): bool;

    /**
     * @throws DescriptionNotFoundException
     */
    public function get(string $id): array;
}
