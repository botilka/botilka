<?php

namespace Botilka\Bridge\ApiPlatform\Description;

interface DescriptionContainerInterface
{
    /**
     * @throws DescriptionNotFoundException
     */
    public function get(string $id): array;

    public function all(): array;
}
