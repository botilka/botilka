<?php

namespace Botilka\Bridge\ApiPlatform\Swagger;

interface SwaggerPayloadNormalizerInterface
{
    public function normalize(array $payload): array;
}
