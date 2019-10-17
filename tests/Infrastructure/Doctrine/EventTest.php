<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Doctrine;

use Botilka\Infrastructure\Doctrine\Event;
use PHPUnit\Framework\TestCase;

final class EventTest extends TestCase
{
    /** @var Event */
    private $event;

    protected function setUp()
    {
        $this->event = new Event('12345678-abcd-1337-affa-f00baababaf0', 123, 'Bar\\Baz', ['foo' => 'bar'], ['baz' => 456], 'Foo\\Domain');
    }

    public function testGetPlayhead(): void
    {
        self::assertSame(123, $this->event->getPlayhead());
    }

    public function testGetRecordedOn(): void
    {
        self::assertInstanceOf(\DateTimeImmutable::class, $this->event->getRecordedOn());
    }

    public function testGetType(): void
    {
        self::assertSame('Bar\\Baz', $this->event->getType());
    }

    public function testGetId(): void
    {
        self::assertSame('12345678-abcd-1337-affa-f00baababaf0', $this->event->getId());
    }

    public function testGetPayload(): void
    {
        self::assertSame(['foo' => 'bar'], $this->event->getPayload());
    }

    public function testGetMetadata(): void
    {
        self::assertSame(['baz' => 456], $this->event->getMetadata());
    }

    public function testGetDomain(): void
    {
        self::assertSame('Foo\\Domain', $this->event->getDomain());
    }
}
