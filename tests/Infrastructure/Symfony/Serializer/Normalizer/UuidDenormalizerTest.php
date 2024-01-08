<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Symfony\Serializer\Normalizer;

use Botilka\Infrastructure\Symfony\Serializer\Normalizer\UuidDenormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @internal
 */
#[CoversClass(UuidDenormalizer::class)]
final class UuidDenormalizerTest extends TestCase
{
    private UuidDenormalizer $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = new UuidDenormalizer();
    }

    #[DataProvider('provideSupportsDenormalizationCases')]
    public function testSupportsDenormalization(bool $expected, string $type): void
    {
        self::assertSame($expected, $this->denormalizer->supportsDenormalization('foo', $type));
    }

    /**
     * @return array<int, array<int, bool|class-string>>
     */
    public static function provideSupportsDenormalizationCases(): iterable
    {
        return [
            [true, UuidInterface::class],
            [false, \stdClass::class],
        ];
    }

    public function testHasCacheableSupportsMethod(): void
    {
        self::assertTrue($this->denormalizer->hasCacheableSupportsMethod());
    }

    public function testDenormalizeValid(): void
    {
        $uuid = Uuid::uuid4();
        /** @var object $denormalizedUuid */
        $denormalizedUuid = $this->denormalizer->denormalize($uuid->toString(), UuidInterface::class);
        self::assertTrue($uuid->equals($denormalizedUuid));
    }

    public function testDenormalizeInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Can not denormalize "foo" as an Uuid.');

        $this->denormalizer->denormalize('foo', UuidInterface::class);
    }
}
