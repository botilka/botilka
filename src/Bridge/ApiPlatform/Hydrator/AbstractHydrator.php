<?php

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Commands and queries are immutable, we can validate them just after denormalization, they won't change.
 */
abstract class AbstractHydrator
{
    private $denormalizer;
    private $validator;

    public function __construct(DenormalizerInterface $denormalizer, ValidatorInterface $validator)
    {
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
    }

    protected function doHydrate($data, string $class)
    {
        $query = $this->denormalizer->denormalize($data, $class);

        $violations = $this->validator->validate($query);

        if (0 < \count($violations)) {
            throw new ValidationException($violations);
        }

        return $query;
    }
}
