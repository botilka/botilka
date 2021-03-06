<?php

declare(strict_types=1);

namespace Botilka\Tests\Projector;

use Botilka\Event\Event;
use Botilka\Projector\DefaultProjectionist;
use Botilka\Projector\Projection;
use Botilka\Projector\Projectionist;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use Botilka\Tests\Fixtures\Domain\StubProjector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class DefaultProjectionistTest extends TestCase
{
    /** @var LoggerInterface|MockObject */
    private $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /** @dataProvider playMatchingProvider */
    public function testPlayMatching(array $context, bool $expectedSkippedNoticeCall, bool $expectedProjectionPlayed): void
    {
        $event = new StubEvent(42);

        $projector = new StubProjector();
        self::assertFalse($projector->onStubEventPlayed);

        $projectionist = new DefaultProjectionist($this->logger, [$projector]);
        self::assertInstanceOf(Projectionist::class, $projectionist);

        $projection = new Projection($event, $context);

        $this->logger->expects($expectedSkippedNoticeCall ? self::once() : self::never())
            ->method('notice')
            ->with(\sprintf('Projection %s::onStubEvent skipped.', StubProjector::class))
        ;

        $projectionist->play($projection);
        self::assertSame($expectedProjectionPlayed, $projector->onStubEventPlayed);
    }

    public function playMatchingProvider(): array
    {
        return [
            [[], false, true],
            [['matching' => 'StubProjector'], false, true],
            [['matching' => 'NonExistent'], true, false],
        ];
    }

    public function testPlayNoHandler(): void
    {
        $event = $this->createMock(Event::class);

        $this->logger->expects(self::once())
            ->method('notice')
            ->with(\sprintf('No projector handler for %s.', \get_class($event)))
        ;

        $projectionist = new DefaultProjectionist($this->logger, [new StubProjector()]);

        $projection = new Projection($event);

        $projectionist->play($projection);
    }

    public function testPlayForEvent(): void
    {
        $event = new StubEvent(42);

        $projector = new StubProjector();
        self::assertFalse($projector->onStubEventPlayed);

        $projectionist = new DefaultProjectionist($this->logger, [$projector]);

        $projectionist->playForEvent($event);

        self::assertTrue($projector->onStubEventPlayed);
    }
}
