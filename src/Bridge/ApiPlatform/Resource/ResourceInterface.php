<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Resource;

/**
 * A resource is either a Command on a Query in a CQRS point of view.
 */
interface ResourceInterface
{
    /**
     * We can't use the property "id" because ItemNormalizer explicitly look for it
     * when deserializing and as we are POSTing a data, it throws an ApiPlatform\Core\Exception\InvalidArgumentException.
     */
    public function getName(): string;

    public function getPayload(): array;
}
