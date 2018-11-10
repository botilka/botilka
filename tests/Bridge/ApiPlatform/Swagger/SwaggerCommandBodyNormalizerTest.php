<?php

namespace Botilka\Tests\Bridge\ApiPlatform\Swagger;

use Botilka\Bridge\ApiPlatform\Swagger\SwaggerCommandBodyNormalizer;
use PHPUnit\Framework\TestCase;

class SwaggerCommandBodyNormalizerTest extends TestCase
{
    /** @dataProvider normalizeProvider */
    public function testNormalize(array $payload, array $expected)
    {
        $normalizer = new SwaggerCommandBodyNormalizer();

        $this->assertSame($expected, $normalizer->normalize($payload));
    }

    public function normalizeProvider(): array
    {
        return [
            // simple
            [
                [
                    'foo' => 'int',
                    'bar' => '?string',
                ],
                [
                    'type' => 'object',
                    'required' => ['foo'],
                    'properties' => [
                            'foo' => [
                                    'type' => 'integer',
                                ],
                            'bar' => [
                                    'type' => 'string',
                                ],
                        ],
                ],
            ],
            // complex
            [
                [
                    'foo' => 'int',
                    'bar' => [
                        'baz' => 'bool',
                        'biz' => [
                            'oof' => '?float',
                        ],
                    ],
                ],
                [
                    'type' => 'object',
                    'required' => ['foo'],
                    'properties' => [
                        'foo' => [
                            'type' => 'integer',
                        ],
                        'bar' => [
                            'type' => 'object',
                            'required' => ['baz'],
                            'properties' => [
                                'baz' => [
                                    'type' => 'boolean',
                                ],
                                'biz' => [
                                    'type' => 'object',
                                    'required' => [],
                                    'properties' => [
                                        'oof' => [
                                            'type' => 'number',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
