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

    protected function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testReplay(): void
    {
        $event = new StubEvent(42);

        $this->logger->expects($this->never())
            ->method('notice');

        $projector = new StubProjector();
        $this->assertFalse($projector->onStubEventPlayed);

        $projectionist = new DefaultProjectionist([$projector], $this->logger);
        $this->assertInstanceOf(Projectionist::class, $projectionist);

        $projection = new Projection($event);

        $projectionist->play($projection);
        $this->assertTrue($projector->onStubEventPlayed);
    }

    public function testReplayNoHandler(): void
    {
        $event = $this->createMock(Event::class);

        $this->logger->expects($this->once())
            ->method('notice')
            ->with(\sprintf('No projector handler for %s.', \get_class($event)));

        $projectionist = new DefaultProjectionist([new StubProjector()], $this->logger);

        $projection = new Projection($event);

        $projectionist->play($projection);
    }
}
