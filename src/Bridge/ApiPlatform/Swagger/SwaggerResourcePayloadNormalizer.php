<?php

namespace Botilka\Bridge\ApiPlatform\Swagger;

final class SwaggerResourcePayloadNormalizer implements SwaggerResourcePayloadNormalizerInterface
{
    public function normalize(array $payload): array
    {
        $parameters = [];

        foreach ($payload as $name => $type) {
            if (!\is_array($type)) {
                $parameters[] = [
                    'name' => $name,
                    'in' => 'query',
                    'required' => '?' !== $type[0],
                    'type' => \str_replace(['?', 'int', 'bool'], ['', 'integer', 'boolean'], $type),
                ];
            } else {
                $this->extractFromArray($name, $type, $parameters);
            }
        }

        return $parameters;
    }

    private function extractFromArray(string $groupName, array $toFlatten, array &$parameters): void
    {
        foreach ($toFlatten as $name => $type) {
            if (!\is_array($type)) {
                $parameters[] = [
                    'name' => $groupName.'['.$name.']',
                    'in' => 'query',
                    'required' => '?' !== $type[0],
                    'type' => \str_replace(['?', 'int', 'bool'], ['', 'integer', 'boolean'], $type),
                ];
            } else {
                $this->extractFromArray($groupName.'['.$name.']', $type, $parameters);
            }
        }
    }
}
