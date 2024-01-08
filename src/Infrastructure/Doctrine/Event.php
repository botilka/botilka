<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(readOnly=true)
 *
 * @ORM\Table(
 *     name="event_store",
 *     indexes={
 *
 *         @ORM\Index(columns={"domain"})
 *     }
 * )
 */
#[ApiResource(operations: [new Get(), new GetCollection()], routePrefix: '/event_store')]
class Event
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     *
     * @ORM\Column(type="uuid")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $recordedOn;

    /**
     * @param class-string              $type
     * @param array<string, mixed>      $payload
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(string $id, /**
     * @ORM\Id
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
        private int $playhead,
        /**
         * @ORM\Column(type="string", length=255)
         */
        private string $type,
        /**
         * @ORM\Column(type="json")
         */
        private array $payload,
        /**
         * @ORM\Column(type="json")
         */
        private ?array $metadata,
        /**
         * @ORM\Column(type="string", length=255)
         */
        private string $domain)
    {
        $this->id = Uuid::fromString($id);
        $this->recordedOn = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getPlayhead(): int
    {
        return $this->playhead;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getRecordedOn(): \DateTimeImmutable
    {
        return $this->recordedOn;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }
}
