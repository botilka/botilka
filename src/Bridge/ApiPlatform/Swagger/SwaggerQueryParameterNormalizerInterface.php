<?php

namespace Botilka\Bridge\ApiPlatform\Swagger;

interface SwaggerQueryParameterNormalizerInterface
{
    public function normalize(array $payload): array;
}
