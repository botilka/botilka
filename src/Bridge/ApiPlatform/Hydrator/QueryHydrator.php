<?php

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Botilka\Application\Query\Query;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class QueryHydrator implements QueryHydratorInterface
{
    private $denormalizer;
    private $validator;

    public function __construct(DenormalizerInterface $denormalizer, ValidatorInterface $validator)
    {
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
    }

    public function hydrate($data, string $class): Query
    {
        /** @var Query $Query */
        $Query = $this->denormalizer->denormalize($data, $class);

        $violations = $this->validator->validate($Query);

        if (0 < \count($violations)) {
            throw new HydrationException($violations);
        }

        return $Query;
    }
}
