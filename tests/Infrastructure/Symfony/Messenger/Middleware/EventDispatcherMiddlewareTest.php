<?php

declare(strict_types=1);

namespace Botilka\Tests\Infrastructure\Symfony\Messenger\Middleware;

use Botilka\Application\Command\CommandResponse;
use Botilka\Application\Command\EventSourcedCommandResponse;
use Botilka\Domain\EventSourcedAggregateRoot;
use Botilka\Event\Event;
use Botilka\Event\EventBus;
use Botilka\EventStore\EventStore;
use Botilka\EventStore\EventStoreConcurrencyException;
use Botilka\Infrastructure\Symfony\Messenger\Middleware\EventDispatcherMiddleware;
use Botilka\Projector\Projectionist;
use Botilka\Repository\EventSourcedRepository;
use Botilka\Repository\EventSourcedRepositoryRegistry;
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;
use Symfony\Component\Messenger\Test\Middleware\MiddlewareTestCase;

/**
 * @internal
 */
#[CoversClass(EventDispatcherMiddleware::class)]
final class EventDispatcherMiddlewareTest extends MiddlewareTestCase
{
    private EventStore&MockObject $eventStore;
    private EventSourcedRepositoryRegistry&MockObject $repositoryRegistry;
    private EventBus&MockObject $eventBus;
    private LoggerInterface&MockObject $logger;
    private MockObject&Projectionist $projectionist;

    protected function setUp(): void
    {
        $this->eventStore = $this->createMock(EventStore::class);
        $this->repositoryRegistry = $this->createMock(EventSourcedRepositoryRegistry::class);
        $this->eventBus = $this->createMock(EventBus::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->projectionist = $this->createMock(Projectionist::class);
    }

    public function testHandledCommandResponse(): void
    {
        $event = new StubEvent(123);
        $commandResponse = new CommandResponse('foo', $event);

        $this->eventStore->expects(self::never())
            ->method('append')
        ;

        $this->addHandleAssertions($event);

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->repositoryRegistry, $this->eventBus, $this->logger, $this->projectionist);
        $middleware->handle($this->getEnvelopeWithHandledStamp($commandResponse), $this->getStackMock());
    }

    #[DataProvider('provideHandledEventSourcedCommandResponseCases')]
    public function testHandledEventSourcedCommandResponse(bool $registryHasRepository): void
    {
        $event = new StubEvent(1337);
        $commandResponse = new EventSourcedCommandResponse('foo', $event, 51, 'FooBar\\Domain', $this->createMock(EventSourcedAggregateRoot::class));

        $this->eventStore->expects($registryHasRepository ? self::never() : self::once())
            ->method('append')
            ->with('foo', 51, $event::class, $event, null, self::isInstanceOf(\DateTimeImmutable::class), 'FooBar\\Domain')
        ;

        $aggregateRootClassName = $commandResponse->getAggregateRoot()::class;
        $this->repositoryRegistry->expects(self::once())
            ->method('has')
            ->with($aggregateRootClassName)
            ->willReturn($registryHasRepository)
        ;

        $repository = $this->createMock(EventSourcedRepository::class);
        if ($registryHasRepository) {
            $repository->expects(self::once())
                ->method('save')
                ->with($commandResponse)
            ;
        }

        $this->repositoryRegistry->expects($registryHasRepository ? self::once() : self::never())
            ->method('get')
            ->with($aggregateRootClassName)
            ->willReturn($repository)
        ;

        $this->addHandleAssertions($event);

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->repositoryRegistry, $this->eventBus, $this->logger, $this->projectionist);
        $middleware->handle($this->getEnvelopeWithHandledStamp($commandResponse), $this->getStackMock());
    }

    /**
     * @return \Generator<array{bool}>
     */
    public static function provideHandledEventSourcedCommandResponseCases(): iterable
    {
        yield [true];
        yield [false];
    }

    public function testHandledNoHandlerForMessageException(): void
    {
        $event = new StubEvent(1337);

        $this->eventBus->expects(self::once())
            ->method('dispatch')
            ->with($event)
            ->willThrowException(new NoHandlerForMessageException())
        ;

        $this->logger->expects(self::once())
            ->method('notice')
            ->with(sprintf('No event handler for %s.', $event::class))
        ;

        $this->projectionist->expects(self::once())
            ->method('play')
        ;

        $commandResponse = new CommandResponse('foo', $event);

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->repositoryRegistry, $this->eventBus, $this->logger, $this->projectionist);
        $middleware->handle($this->getEnvelopeWithHandledStamp($commandResponse), $this->getStackMock());
    }

    public function testHandledEventStoreConcurrencyException(): void
    {
        $event = new StubEvent(1337);

        $this->eventStore->expects(self::once())
            ->method('append')
            ->willThrowException(new EventStoreConcurrencyException('bar message'))
        ;

        $this->eventBus->expects(self::never())
            ->method('dispatch')
        ;

        $this->projectionist->expects(self::never())
            ->method('play')
        ;

        $this->logger->expects(self::once())
            ->method('error')
            ->with('bar message')
        ;

        $this->expectException(EventStoreConcurrencyException::class);
        $this->expectExceptionMessage('bar message');

        $commandResponse = new EventSourcedCommandResponse('foo', $event, 51, 'FooBar\\Domain', $this->createMock(EventSourcedAggregateRoot::class));

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->repositoryRegistry, $this->eventBus, $this->logger, $this->projectionist);
        $middleware->handle($this->getEnvelopeWithHandledStamp($commandResponse), $this->getStackMock());
    }

    public function testHandleddNotCommandResponse(): void
    {
        $this->eventStore->expects(self::never())
            ->method('append')
        ;

        $this->eventBus->expects(self::never())
            ->method('dispatch')
        ;

        $this->projectionist->expects(self::never())
            ->method('play')
        ;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Result must be an instance of Botilka\Application\Command\CommandResponse, stdClass given.');

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->repositoryRegistry, $this->eventBus, $this->logger, $this->projectionist);
        $middleware->handle($this->getEnvelopeWithHandledStamp(new \stdClass()), $this->getStackMock());
    }

    public function testSent(): void
    {
        $stamp = new SentStamp(self::class, 'this');
        $message = new \stdClass();
        $message->foo = 'bar';

        $envelope = new Envelope($message, [$stamp]);

        $this->eventStore->expects(self::never())
            ->method('append')
        ;

        $this->eventBus->expects(self::never())
            ->method('dispatch')
        ;

        $this->projectionist->expects(self::never())
            ->method('play')
        ;

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->repositoryRegistry, $this->eventBus, $this->logger, $this->projectionist);
        $middleware->handle($envelope, $this->getStackMock());
    }

    private function addHandleAssertions(Event $event): void
    {
        $this->eventBus->expects(self::once())
            ->method('dispatch')
            ->with($event)
        ;

        $this->logger->expects(self::never())
            ->method('error')
        ;

        $this->projectionist->expects(self::once())
            ->method('play')
        ;
    }

    private function getEnvelopeWithHandledStamp(object $result): Envelope
    {
        $stamp = new HandledStamp($result, 'fooCallableName');
        $message = new \stdClass();
        $message->foo = 'bar';

        return new Envelope($message, [$stamp]);
    }
}
