<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Swagger;

interface SwaggerPayloadNormalizerInterface
{
    public function normalize(array $payload): array;
}
