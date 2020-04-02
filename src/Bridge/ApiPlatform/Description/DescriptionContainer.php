<?php

declare(strict_types=1);

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

    /**
     * @param array<string, array<string, mixed>> $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @throws DescriptionNotFoundException
     *
     * @return array<string, mixed>
     */
    public function get(string $name): array
    {
        // avoid a function call
        if (!isset($this->data[$name])) {
            throw new DescriptionNotFoundException(\sprintf('Description "%s" was not found. Possible values: "%s".', $name, \implode('", "', \array_keys($this->data))));
        }

        return $this->data[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * @return \ArrayIterator<string, array<string, mixed>>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->data);
    }
}
