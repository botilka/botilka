<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Botilka\Bridge\ApiPlatform\Resource\Query;
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

    /**
     * @param mixed $data
     *
     * @return Command|Query
     */
    protected function doHydrate($data, string $class)
    {
        /** @var Command|Query $hydrated */
        $hydrated = $this->denormalizer->denormalize($data, $class);

        $violations = $this->validator->validate($hydrated);

        if (0 < \count($violations)) {
            throw new ValidationException($violations);
        }

        return $hydrated;
    }
}
