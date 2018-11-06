<?php

namespace Botilka\Bridge\ApiPlatform\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *     routePrefix="/cqrs",
 *     itemOperations={
 *         "get"={"path"="/description/queries/{id}"}
 *     },
 *     collectionOperations={
 *         "get"={"path"="/description/queries", "pagination_enabled"=false}
 *     }
 * )
 */
final class Query implements ResourceInterface
{
    /** @ApiProperty(identifier=true) */
    private $name;

    /** @var array An object representing the query arguments */
    private $payload;

    public function __construct(string $name, array $payload)
    {
        $this->name = $name;
        $this->payload = $payload;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
