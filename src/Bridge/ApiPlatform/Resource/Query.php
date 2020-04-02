<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
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

    // An object representing the query arguments
    private $payload;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(string $name, array $payload)
    {
        $this->name = $name;
        $this->payload = $payload;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, mixed> $payload
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
