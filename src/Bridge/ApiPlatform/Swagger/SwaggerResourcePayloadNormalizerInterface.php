<?php

namespace Botilka\Bridge\ApiPlatform\Swagger;

interface SwaggerResourcePayloadNormalizerInterface
{
    public function normalize(array $payload): array;
}
