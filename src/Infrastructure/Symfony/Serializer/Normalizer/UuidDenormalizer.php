<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Symfony\Serializer\Normalizer;

use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class UuidDenormalizer implements DenormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @param string               $data
     * @param class-string         $type
     * @param array<string, mixed> $context
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): UuidInterface
    {
        try {
            return Uuid::fromString($data);
        } catch (InvalidUuidStringException) {
            throw new \InvalidArgumentException(sprintf('Can not denormalize %s as an Uuid.', json_encode($data, \JSON_THROW_ON_ERROR)));
        }
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return UuidInterface::class === $type && \is_string($data);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
