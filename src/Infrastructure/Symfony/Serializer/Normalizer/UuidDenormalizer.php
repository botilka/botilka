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
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        try {
            return Uuid::fromString($data);
        } catch (InvalidUuidStringException $e) {
            throw new \InvalidArgumentException(\sprintf('Can not denormalize %s as an Uuid.', \json_encode($data)));
        }
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return UuidInterface::class === $type;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
