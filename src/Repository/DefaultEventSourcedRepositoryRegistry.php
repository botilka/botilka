<?php

declare(strict_types=1);

namespace Botilka\Repository;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * @internal
 */
final class DefaultEventSourcedRepositoryRegistry implements EventSourcedRepositoryRegistry
{
    /**
     * @param array<class-string, EventSourcedRepository> $repositories
     */
    public function __construct(
        private array $repositories = [],
    ) {}

    /**
     * @param class-string $className
     */
    public function get($className): EventSourcedRepository
    {
        if (!isset($this->repositories[$className])) {
            throw new ServiceNotFoundException("Event sourced repository for aggregate root '{$className}' not found.");
        }

        return $this->repositories[$className];
    }

    /**
     * @param class-string $className
     */
    public function has($className): bool
    {
        return isset($this->repositories[$className]);
    }
}
