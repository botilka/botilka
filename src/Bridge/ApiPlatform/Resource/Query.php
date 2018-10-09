<?php

namespace Botilka\Bridge\ApiPlatform\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     routePrefix="/cqrs",
 *     itemOperations={"get"},
 *     collectionOperations={
 *         "get"={"normalization_context"={"groups"={"read"}}}
 *     }
 * )
 */
final class Query implements ResourceInterface
{
    /** @ApiProperty(identifier=true) */
    private $id;

    /** @var array An object representing the query arguments */
    private $payload;

    public function __construct(string $id, array $payload)
    {
        $this->id = $id;
        $this->payload = $payload;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @Groups("read")
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
