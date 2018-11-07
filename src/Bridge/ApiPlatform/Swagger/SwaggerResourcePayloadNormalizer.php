<?php

namespace Botilka\Bridge\ApiPlatform\Swagger;

final class SwaggerResourcePayloadNormalizer implements SwaggerResourcePayloadNormalizerInterface
{
    // @todo normalize array
    public function normalize(array $payload): array
    {
        $parameters = [];

        foreach ($payload as $name => $type) {
            $parameters[] = [
                'name' => $name,
                'in' => 'query',
                'required' => '?' !== $type[0],
                'type' => \str_replace('?', '', $type),
            ];
        }

        return $parameters;
    }
}
