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
use Botilka\Tests\Fixtures\Domain\StubEvent;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Test\Middleware\MiddlewareTestCase;

final class EventDispatcherMiddlewareTest extends MiddlewareTestCase
{
    /** @var EventStore|MockObject */
    private $eventStore;
    /** @var ContainerInterface|MockObject */
    private $repositoryRegistry;
    /** @var EventBus|MockObject */
    private $eventBus;
    /** @var LoggerInterface|MockObject */
    private $logger;
    /** @var Projectionist|MockObject */
    private $projectionist;

    protected function setUp()
    {
        $this->eventStore = $this->createMock(EventStore::class);
        $this->repositoryRegistry = $this->createMock(ContainerInterface::class);
        $this->eventBus = $this->createMock(EventBus::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->projectionist = $this->createMock(Projectionist::class);
    }

    private function addHandleAssertions(Event $event): void
    {
        $this->eventBus->expects($this->once())
            ->method('dispatch')
            ->with($event);

        $this->logger->expects($this->never())
            ->method('error');

        $this->projectionist->expects($this->once())
            ->method('play');
    }

    public function testHandleCommandResponse(): void
    {
        $event = new StubEvent(123);
        $commandResponse = new CommandResponse('foo', $event);

        $this->eventStore->expects($this->never())
            ->method('append');

        $this->addHandleAssertions($event);

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->repositoryRegistry, $this->eventBus, $this->logger, $this->projectionist);
        $middleware->handle($this->getEnvelopeWithHandledStamp($commandResponse), $this->getStackMock());
    }

    /** @dataProvider handleEventSourcedCommandResponseProvider */
    public function testHandleEventSourcedCommandResponse(EventSourcedCommandResponse $commandResponse, bool $registryHasRepository): void
    {
        $event = $commandResponse->getEvent();
        $eventStoreAppendExpected = EventSourcedCommandResponse::class === \get_class($commandResponse);

        $this->eventStore->expects(!$registryHasRepository ? $this->once() : $this->never())
            ->method('append')
            ->with('foo', 51, \get_class($event), $event, null, $this->isInstanceOf(\DateTimeImmutable::class), 'FooBar\\Domain');

        $aggregateRootClassName = \get_class($commandResponse->getAggregateRoot());
        $this->repositoryRegistry->expects($this->once())
            ->method('has')
            ->with($aggregateRootClassName)
            ->willReturn($registryHasRepository);

        $repository = $this->createMock(EventSourcedRepository::class);
        if ($registryHasRepository) {
            $repository->expects($this->once())
                ->method('save')
                ->with($commandResponse);
        }

        $this->repositoryRegistry->expects($registryHasRepository ? $this->once() : $this->never())
            ->method('get')
            ->with($aggregateRootClassName)
            ->willReturn($repository);

        $this->addHandleAssertions($event);

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->repositoryRegistry, $this->eventBus, $this->logger, $this->projectionist);
        $middleware->handle($this->getEnvelopeWithHandledStamp($commandResponse), $this->getStackMock());
    }

    public function handleEventSourcedCommandResponseProvider(): array
    {
        $event = new StubEvent(1337);

        return [
            [new EventSourcedCommandResponse('foo', $event, 51, 'FooBar\\Domain', $this->createMock(EventSourcedAggregateRoot::class)), true],
            [new EventSourcedCommandResponse('foo', $event, 51, 'FooBar\\Domain', $this->createMock(EventSourcedAggregateRoot::class)), false],
        ];
    }

    public function testHandleNoHandlerForMessageException(): void
    {
        $event = new StubEvent(1337);

        $this->eventBus->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willThrowException(new NoHandlerForMessageException());

        $this->logger->expects($this->once())
            ->method('notice')
            ->with(\sprintf('No event handler for %s.', \get_class($event)));

        $this->projectionist->expects($this->once())
            ->method('play');

        $commandResponse = new CommandResponse('foo', $event);

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->repositoryRegistry, $this->eventBus, $this->logger, $this->projectionist);
        $middleware->handle($this->getEnvelopeWithHandledStamp($commandResponse), $this->getStackMock());
    }

    /**
     * @expectedException \Botilka\EventStore\EventStoreConcurrencyException
     * @expectedExceptionMessage bar message
     */
    public function testHandleEventStoreConcurrencyException(): void
    {
        $event = new StubEvent(1337);

        $this->eventStore->expects($this->once())
            ->method('append')
            ->willThrowException(new EventStoreConcurrencyException('bar message'));

        $this->eventBus->expects($this->never())
            ->method('dispatch');

        $this->projectionist->expects($this->never())
            ->method('play');

        $this->logger->expects($this->once())
            ->method('error')
            ->with('bar message');

        $commandResponse = new EventSourcedCommandResponse('foo', $event, 51, 'FooBar\\Domain', $this->createMock(EventSourcedAggregateRoot::class));

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->repositoryRegistry, $this->eventBus, $this->logger, $this->projectionist);
        $middleware->handle($this->getEnvelopeWithHandledStamp($commandResponse), $this->getStackMock());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Result must be an instance of Botilka\Application\Command\CommandResponse, stdClass given.
     */
    public function testHandleNotCommandResponse(): void
    {
        $this->eventStore->expects($this->never())
            ->method('append');

        $this->eventBus->expects($this->never())
            ->method('dispatch');

        $this->projectionist->expects($this->never())
            ->method('play');

        $middleware = new EventDispatcherMiddleware($this->eventStore, $this->repositoryRegistry, $this->eventBus, $this->logger, $this->projectionist);
        $middleware->handle($this->getEnvelopeWithHandledStamp(new \stdClass()), $this->getStackMock());
    }

    /**
     * @param mixed $result
     */
    private function getEnvelopeWithHandledStamp($result): Envelope
    {
        $stamp = new HandledStamp($result, 'fooCallableName');
        $message = new \stdClass();
        $message->foo = 'bar';

        return new Envelope($message, $stamp);
    }
}
