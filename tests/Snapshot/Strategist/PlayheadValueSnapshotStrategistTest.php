<?php

declare(strict_types=1);

namespace Botilka\Tests\Snapshot\Strategist;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Snapshot\Strategist\PlayheadValueSnapshotStrategist;
use PHPUnit\Framework\TestCase;

final class PlayheadValueSnapshotStrategistTest extends TestCase
{
    public function tesGetEachPlayheadDefault()
    {
        $strategist = new PlayheadValueSnapshotStrategist();
        $this->assertSame(5, $strategist->getEachPlayhead());
    }

    /** @dataProvider mustSnapshotProvider */
    public function testMustSnapshot(bool $expected, int $playhead, int $eachPlayhead): void
    {
        $strategist = new PlayheadValueSnapshotStrategist($eachPlayhead);

        $aggregateRoot = $this->createMock(EventSourcedAggregateRoot::class);
        $aggregateRoot->expects($this->once())
            ->method('getPlayhead')
            ->willReturn($playhead);

        $this->assertSame($expected, $strategist->mustSnapshot($aggregateRoot));
    }

    public function mustSnapshotProvider(): array
    {
        return [
            [false, 0, 20],
            [false, 10, 20],
            [true, 19, 20],
            [false, 20, 20],
        ];
    }
}
