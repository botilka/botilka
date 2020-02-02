<?php

declare(strict_types=1);

namespace Botilka\Repository;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * This maps an event sourced aggregate root class name to it's repository.
 *
 * @internal
 */
final class EventSourcedRepositoryRegistry implements ContainerInterface
{
    private $repositories;

    public function __construct(array $repositories = [])
    {
        $this->repositories = $repositories;
    }

    /**
     * @param string $className
     */
    public function get($className): EventSourcedRepository
    {
        if (!isset($this->repositories[$className])) {
            throw new ServiceNotFoundException("Event sourced repository for aggregate root '{$className}' not found.");
        }

        return $this->repositories[$className];
    }

    /**
     * @param string $className
     */
    public function has($className): bool
    {
        return isset($this->repositories[$className]);
    }
}
