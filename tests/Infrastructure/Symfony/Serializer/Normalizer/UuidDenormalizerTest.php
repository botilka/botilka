<?php

namespace Botilka\Tests\Infrastructure\Symfony\Serializer\Normalizer;

use Botilka\Infrastructure\Symfony\Serializer\Normalizer\UuidDenormalizer;
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

    public function testDenormalizeValid()
    {
        $uuid = Uuid::uuid4();
        $this->assertTrue($uuid->equals($this->denormalizer->denormalize($uuid->toString(), UuidInterface::class)));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Can not denormalize "foo" as an Uuid.
     */
    public function testDenormalizeInvalid()
    {
        $this->denormalizer->denormalize('foo', UuidInterface::class);
    }
}
