<?php

declare(strict_types=1);

namespace Botilka\Tests\Snapshot\Strategist;

use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Snapshot\Strategist\PlayheadValueSnapshotStrategist;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PlayheadValueSnapshotStrategist::class)]
final class PlayheadValueSnapshotStrategistTest extends TestCase
{
    public function testGetEachPlayheadDefault(): void
    {
        $strategist = new PlayheadValueSnapshotStrategist();
        self::assertSame(5, $strategist->getEachPlayhead());
    }

    #[DataProvider('provideMustSnapshotCases')]
    public function testMustSnapshot(bool $expected, int $playhead, int $eachPlayhead): void
    {
        $strategist = new PlayheadValueSnapshotStrategist($eachPlayhead);

        $aggregateRoot = $this->createMock(EventSourcedAggregateRoot::class);
        $aggregateRoot->expects(self::once())
            ->method('getPlayhead')
            ->willReturn($playhead)
        ;

        self::assertSame($expected, $strategist->mustSnapshot($aggregateRoot));
    }

    /**
     * @return array<int, array<int, bool|int>>
     */
    public static function provideMustSnapshotCases(): iterable
    {
        return [
            [false, 0, 20],
            [false, 10, 20],
            [true, 19, 20],
            [false, 20, 20],
        ];
    }
}
