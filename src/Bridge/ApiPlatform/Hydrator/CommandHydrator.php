<?php

namespace Botilka\Bridge\ApiPlatform\Hydrator;

use Botilka\Application\Command\Command;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CommandHydrator implements CommandHydratorInterface
{
    private $denormalizer;
    private $validator;

    public function __construct(DenormalizerInterface $denormalizer, ValidatorInterface $validator)
    {
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
    }

    public function hydrate($data, string $class): Command
    {
        /** @var Command $command */
        $command = $this->denormalizer->denormalize($data, $class);

        $violations = $this->validator->validate($command);

        if (0 < \count($violations)) {
            throw new HydrationException($violations);
        }

        return $command;
    }
}
