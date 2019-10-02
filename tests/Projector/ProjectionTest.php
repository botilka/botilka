<?php

declare(strict_types=1);

namespace Botilka\Tests\Projector;

use Botilka\Projector\Projection;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;

final class ProjectionTest extends TestCase
{
    public function testGetEvent(): void
    {
        $event = new StubEvent(42);
        $projection = new Projection($event, ['foo' => 'bar']);

        self::assertInstanceOf(Projection::class, $projection);
        self::assertSame($event, $projection->getEvent());
        self::assertSame(['foo' => 'bar'], $projection->getContext());
    }
}
