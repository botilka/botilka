<?php

namespace Botilka\Bridge\ApiPlatform\Resource;

/**
 * A resource is either a Command on a Query in a CQRS point of view.
 */
interface ResourceInterface
{
    public function getId(): string;

    public function getPayload(): array;
}
