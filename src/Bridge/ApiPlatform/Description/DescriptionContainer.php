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
    public function get(string $id): array
    {
        // avoid a function call
        if (!isset($this->data[$id])) {
            throw new DescriptionNotFoundException(
                \sprintf('Description "%s" was not found. Possible values: "%s".', $id, \implode('", "', \array_keys($this->data)))
            );
        }

        return $this->data[$id];
    }

    public function all(): array
    {
        return $this->data;
    }
}
