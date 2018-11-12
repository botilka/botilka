<?php

namespace Botilka\Tests\Infrastructure\Doctrine;

use Botilka\Infrastructure\Doctrine\Event;
use PHPUnit\Framework\TestCase;

final class EventTest extends TestCase
{
    /** @var Event */
    private $event;

    public function setUp()
    {
        $this->event = new Event('12345678-abcd-1337-affa-f00baababaf0', 123, 'Bar\\Baz', ['foo' => 'bar'], ['baz' => 456]);
    }

    public function testGetPlayhead(): void
    {
        $this->assertSame(123, $this->event->getPlayhead());
    }

    public function testGetRecordedOn(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->event->getRecordedOn());
    }

    public function testGetType(): void
    {
        $this->assertSame('Bar\\Baz', $this->event->getType());
    }

    public function testGetId(): void
    {
        $this->assertSame('12345678-abcd-1337-affa-f00baababaf0', $this->event->getId());
    }

    public function testGetPayload(): void
    {
        $this->assertSame(['foo' => 'bar'], $this->event->getPayload());
    }

    public function testGetMetadata(): void
    {
        $this->assertSame(['baz' => 456], $this->event->getMetadata());
    }
}
