<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Description;

/**
 * @extends \IteratorAggregate<string, array<string, mixed>>
 */
interface DescriptionContainerInterface extends \IteratorAggregate
{
    public function has(string $name): bool;

    /**
     * @throws DescriptionNotFoundException
     *
     * @return array<string, mixed>
     */
    public function get(string $name): array;
}
