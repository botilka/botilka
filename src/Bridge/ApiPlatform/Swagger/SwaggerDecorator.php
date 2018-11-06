<?php

namespace Botilka\Bridge\ApiPlatform\Swagger;

use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @todo supprimer ?
 */
final class SwaggerDecorator implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private $decorated;
    private $descriptionContainer;

    public function __construct(NormalizerInterface $decorated, DescriptionContainerInterface $descriptionContainer)
    {
        $this->decorated = $decorated;
        $this->descriptionContainer = $descriptionContainer;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        return $docs;

        foreach ($this->descriptionContainer as $commandName => $commmandDescription) {
            $parameters = $this->extractParameters($commmandDescription['payload']);
            $path = '/api/cqrs/commands/'.$commandName;
            $docs['paths'][$path]['post']['parameters'] = $parameters;
            $docs['paths'][$path]['post']['tags'] = ['Command'];
        }

        return $docs;
    }

    private function extractParameters(array $parameterDescription): array
    {
        $parameters = [];
        foreach ($parameterDescription as $parameterName => $parameterType) {
            if (!\is_scalar($parameterType)) {
                $parameters[] = $this->extractParameters($parameterType);
                continue;
            }
            $parameters[] = [
                'name' => null === $parameterParent ? $parameterName : $parameterParent.'['.$parameterName.']',
                'in' => 'query',
                'description' => \str_replace('?', '', $parameterType, $optional),
                'required' => 0 === $optional,
            ];
        }

        return $parameters;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->decorated instanceof CacheableSupportsMethodInterface && $this->decorated->hasCacheableSupportsMethod();
    }
}
