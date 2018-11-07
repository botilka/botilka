<?php

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
            throw new HydrationException($violations);
        }

        return $query;
    }
}
