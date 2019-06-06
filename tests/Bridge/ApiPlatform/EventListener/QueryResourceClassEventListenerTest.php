<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\EventListener;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use ApiPlatform\Core\EventListener\EventPriorities;
use Botilka\Application\Query\QueryBus;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\EventListener\QueryResourceClassEventListener;
use Botilka\Bridge\ApiPlatform\Hydrator\QueryHydratorInterface;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Botilka\Tests\Fixtures\Application\Query\SimpleQuery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class QueryResourceClassEventListenerTest extends TestCase
{
    /** @var QueryResourceClassEventListener */
    private $listener;
    /** @var DescriptionContainerInterface|MockObject */
    private $descriptionContainer;
    /** @var QueryBus|MockObject */
    private $queryBus;
    /** @var QueryHydratorInterface|MockObject */
    private $hydrator;

    protected function setUp()
    {
        $this->queryBus = $this->createMock(QueryBus::class);
        $this->descriptionContainer = $this->createMock(DescriptionContainerInterface::class);
        $this->hydrator = $this->createMock(QueryHydratorInterface::class);
        $this->listener = new QueryResourceClassEventListener($this->queryBus, $this->descriptionContainer, $this->hydrator);
    }

    public function testOnKernelRequestIncorrectApiResourceClass(): void
    {
        $event = $this->getEvent(['_api_resource_class' => \stdClass::class]);

        $this->queryBus->expects($this->never())->method('dispatch');

        $this->listener->onKernelRequest($event);
        $this->assertNull($event->getRequest()->attributes->get('data'));
        $this->assertTrue($event->getRequest()->attributes->get('_api_receive'));
    }

    /** @dataProvider onKernelRequestQueryNotExistingProvider */
    public function testOnKernelRequestQueryNotExisting(?string $queryName): void
    {
        $event = $this->getEvent(['_api_resource_class' => Query::class, '_api_item_operation_name' => $queryName]);

        if (null !== $queryName) {
            $this->descriptionContainer->expects($this->once())
                ->method('has')
                ->with($queryName)
                ->willReturn(false);
        }

        $this->queryBus->expects($this->never())->method('dispatch');

        $this->listener->onKernelRequest($event);
        $this->assertNull($event->getRequest()->attributes->get('data'));
        $this->assertTrue($event->getRequest()->attributes->get('_api_receive'));
    }

    public function onKernelRequestQueryNotExistingProvider(): array
    {
        return [
            [null],
            ['foo'],
        ];
    }

    public function testOnKernelRequest(): void
    {
        $event = $this->getEvent(['_api_resource_class' => Query::class, '_api_item_operation_name' => 'foo'], ['foo' => 'bar']);

        $descriptionContainer = new DescriptionContainer(['foo' => [
            'class' => 'Foo\\Bar',
            'payload' => ['some' => 'string'],
        ]]);

        $query = new SimpleQuery('foo');

        $this->hydrator->expects($this->once())
            ->method('hydrate')
            ->with(['foo' => 'bar'], 'Foo\\Bar')
            ->willReturn($query);

        $this->queryBus->expects($this->once())
            ->method('dispatch')
            ->with($query)
            ->willReturn('baz');

        $listener = new QueryResourceClassEventListener($this->queryBus, $descriptionContainer, $this->hydrator);
        $listener->onKernelRequest($event);

        $this->assertSame('baz', $event->getRequest()->attributes->get('data'));
        $this->assertFalse($event->getRequest()->attributes->get('_api_receive'));
    }

    public function testOnKernelRequestValidationException(): void
    {
        $event = $this->getEvent(['_api_resource_class' => Query::class, '_api_item_operation_name' => 'foo'], ['foo' => 'bar']);

        $descriptionContainer = new DescriptionContainer(['foo' => [
            'class' => 'Foo\\Bar',
            'payload' => ['some' => 'string'],
        ]]);

        $exception = new ValidationException($this->createMock(ConstraintViolationListInterface::class));

        $this->hydrator->expects($this->once())
            ->method('hydrate')
            ->with(['foo' => 'bar'], 'Foo\\Bar')
            ->willThrowException($exception);

        $this->queryBus->expects($this->never())->method('dispatch');

        $listener = new QueryResourceClassEventListener($this->queryBus, $descriptionContainer, $this->hydrator);
        $listener->onKernelRequest($event);

        $this->assertSame($exception->getConstraintViolationList(), $event->getRequest()->attributes->get('data'));
        $this->assertFalse($event->getRequest()->attributes->get('_api_receive'));
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame([
            KernelEvents::REQUEST => [
                ['onKernelRequest', EventPriorities::PRE_READ],
            ],
        ], QueryResourceClassEventListener::getSubscribedEvents());
    }

    private function getEvent(array $attributes, array $query = []): RequestEvent
    {
        $request = new Request($query, [], $attributes + ['_api_receive' => true]);

        return new RequestEvent($this->createMock(KernelInterface::class), $request, HttpKernelInterface::MASTER_REQUEST);
    }
}
