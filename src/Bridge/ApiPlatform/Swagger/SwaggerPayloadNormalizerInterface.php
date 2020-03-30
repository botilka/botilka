<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\Swagger;

interface SwaggerPayloadNormalizerInterface
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function normalize(array $payload): array;
}
