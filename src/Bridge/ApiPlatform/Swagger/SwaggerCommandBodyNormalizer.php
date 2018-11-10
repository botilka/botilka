<?php

namespace Botilka\Bridge\ApiPlatform\Swagger;

final class SwaggerCommandBodyNormalizer implements SwaggerPayloadNormalizerInterface
{
    public function normalize(array $payload): array
    {
        $body = [
            'type' => 'object',
            'required' => [],
            'properties' => [],
        ];

        foreach ($payload as $name => $type) {
            if (!\is_array($type)) {
                $body['properties'][$name] = [
                    'type' => \str_replace(['?', 'int', 'bool', 'float'], ['', 'integer', 'boolean', 'number'], $type),
                ];
                if ('?' !== $type[0]) {
                    $body['required'][] = $name;
                }
            } else {
                $body['properties'][$name] = $this->normalize($type);
            }
        }

        return $body;
    }
}
