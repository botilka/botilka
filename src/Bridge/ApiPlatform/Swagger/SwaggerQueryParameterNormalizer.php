<?php

namespace Botilka\Bridge\ApiPlatform\Swagger;

final class SwaggerQueryParameterNormalizer implements SwaggerPayloadNormalizerInterface
{
    public function normalize(array $payload): array
    {
        $parameters = [];

        foreach ($payload as $name => $type) {
            $this->flatten($name, $type, $parameters);
        }

        return $parameters;
    }

    private function flatten(string $name, $type, array &$parameters): void
    {
        if (\is_array($type)) {
            foreach ($type as $childName => $childType) {
                $flattenedChildName = $name.'['.$childName.']';
                if (\is_array($childType)) {
                    $this->flatten($flattenedChildName, $childType, $parameters);
                } else {
                    $parameters[] = $this->getParameterDescription($flattenedChildName, $childType);
                }
            }
        } else {
            $parameters[] = $this->getParameterDescription($name, $type);
        }
    }

    private function getParameterDescription(string $name, string $type): array
    {
        return [
            'name' => $name,
            'in' => 'query',
            'required' => '?' !== $type[0],
            'type' => \str_replace(['?', 'int', 'bool'], ['', 'integer', 'boolean'], $type),
        ];
    }
}
