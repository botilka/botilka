<?php

declare(strict_types=1);

namespace Botilka\Tests\Projector;

use Botilka\Projector\DefaultProjection;
use Botilka\Projector\Projection;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\TestCase;

class DefaultProjectionTest extends TestCase
{
    public function testGetEvent()
    {
        $event = new StubEvent(42);
        $projection = new DefaultProjection($event);

        $this->assertInstanceOf(Projection::class, $projection);
        $this->assertSame($event, $projection->getEvent());
    }
}
