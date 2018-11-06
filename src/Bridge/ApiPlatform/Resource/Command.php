<?php

namespace Botilka\Bridge\ApiPlatform\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Botilka\Bridge\ApiPlatform\Action\CommandAction;

/**
 * @ApiResource(
 *     routePrefix="/cqrs",
 *     itemOperations={
 *         "get"={"path"="/description/commands/{id}"}
 *     },
 *     collectionOperations={
 *         "post"={"controller"=CommandAction::class},
 *         "get"={"path"="/description/commands", "pagination_enabled"=false}
 *     }
 * )
 */
final class Command implements ResourceInterface
{
    /** @ApiProperty(identifier=true) */
    private $name;

    /** @var array An object representing the command arguments */
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
