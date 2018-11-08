<?php

namespace Botilka\Tests\Bridge\ApiPlatform\Swagger;

use Botilka\Bridge\ApiPlatform\Swagger\SwaggerQueryParameterNormalizer;
use PHPUnit\Framework\TestCase;

class SwaggerQueryParameterNormalizerTest extends TestCase
{
    /** @dataProvider normalizeProvider */
    public function testNormalize(array $payload, array $expected)
    {
        $normalizer = new SwaggerQueryParameterNormalizer();

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
                    [
                        'name' => 'foo',
                        'in' => 'query',
                        'required' => true,
                        'type' => 'integer',
                    ],
                    [
                        'name' => 'bar',
                        'in' => 'query',
                        'required' => false,
                        'type' => 'string',
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
                    [
                        'name' => 'foo',
                        'in' => 'query',
                        'required' => true,
                        'type' => 'integer',
                    ],
                    [
                        'name' => 'bar[baz]',
                        'in' => 'query',
                        'required' => true,
                        'type' => 'boolean',
                    ],
                    [
                        'name' => 'bar[biz][oof]',
                        'in' => 'query',
                        'required' => false,
                        'type' => 'float',
                    ],
                ],
            ],
        ];
    }
}
