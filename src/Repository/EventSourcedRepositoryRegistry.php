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
     * @param mixed $className
     *
     * @return EventSourcedRepository
     */
    public function get($className)
    {
        if (!isset($this->repositories[$className])) {
            throw new ServiceNotFoundException("Event sourced repository for aggregate root '{$className}' not found.");
        }

        return $this->repositories[$className];
    }

    public function has($className)
    {
        return isset($this->repositories[$className]);
    }
}
