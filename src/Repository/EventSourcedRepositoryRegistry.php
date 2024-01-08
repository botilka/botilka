<?php

declare(strict_types=1);

namespace Botilka\Repository;

use Psr\Container\ContainerInterface;

/**
 * This maps an event sourced aggregate root class name to it's repository.
 * Nothing more than a @see ContainerInterface,
 * but with strong typing.
 *
 * @internal
 */
interface EventSourcedRepositoryRegistry
{
    /**
     * @param class-string $className
     */
    public function get($className): EventSourcedRepository;

    /**
     * @param class-string $className
     */
    public function has($className): bool;
}
