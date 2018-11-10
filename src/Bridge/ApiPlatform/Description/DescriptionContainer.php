<?php

namespace Botilka\Bridge\ApiPlatform\Description;

use Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler\ApiPlatformDescriptionContainerPass;

/**
 * Will handle Commands & Queries descriptions in different instances.
 *
 * @see ApiPlatformDescriptionContainerPass
 */
final class DescriptionContainer implements DescriptionContainerInterface
{
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @throws DescriptionNotFoundException
     */
    public function get(string $name): array
    {
        // avoid a function call
        if (!isset($this->data[$name])) {
            throw new DescriptionNotFoundException(
                \sprintf('Description "%s" was not found. Possible values: "%s".', $name, \implode('", "', \array_keys($this->data)))
            );
        }

        return $this->data[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
}
