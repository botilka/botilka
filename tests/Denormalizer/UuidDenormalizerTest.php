<?php

namespace Botilka\Tests\Denormalizer;

use Botilka\Denormalizer\UuidDenormalizer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UuidDenormalizerTest extends TestCase
{
    /** @var UuidDenormalizer */
    private $denormalizer;

    public function setUp()
    {
        $this->denormalizer = new UuidDenormalizer();
    }

    /** @dataProvider supportsDenormalizationProvider */
    public function testSupportsDenormalization(bool $expected, string $type)
    {
        $this->assertSame($expected, $this->denormalizer->supportsDenormalization('foo', $type));
    }

    public function supportsDenormalizationProvider(): array
    {
        return  [
            [true, UuidInterface::class],
            [false, \stdClass::class],
        ];
    }

    public function testHasCacheableSupportsMethod()
    {
        $this->assertTrue($this->denormalizer->hasCacheableSupportsMethod());
    }

    public function testDenormalize()
    {
        $uuid = Uuid::uuid4();
        $this->assertTrue($uuid->equals($this->denormalizer->denormalize($uuid->toString(), UuidInterface::class)));
    }
}
