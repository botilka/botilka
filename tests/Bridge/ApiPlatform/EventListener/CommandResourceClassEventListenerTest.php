<?php

declare(strict_types=1);

namespace Botilka\Tests\Bridge\ApiPlatform\EventListener;

use ApiPlatform\Core\EventListener\EventPriorities;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\EventListener\CommandResourceClassEventListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

final class CommandResourceClassEventListenerTest extends TestCase
{
    /** @var CommandResourceClassEventListener */
    private $listener;
    /** @var DescriptionContainerInterface|MockObject */
    private $descriptionContainer;

    protected function setUp()
    {
        $this->descriptionContainer = $this->createMock(DescriptionContainerInterface::class);
        $this->listener = new CommandResourceClassEventListener($this->descriptionContainer);
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertSame([
            KernelEvents::REQUEST => [
                ['onKernelRequest', EventPriorities::PRE_DESERIALIZE],
            ],
        ], CommandResourceClassEventListener::getSubscribedEvents());
    }

    public function testOnKernelRequestNotPost(): void
    {
        $event = $this->getEvent();

        $this->descriptionContainer->expects(self::never())
            ->method('get')
        ;

        $this->listener->onKernelRequest($event);
        self::assertNull($event->getRequest()->attributes->get('_api_resource_class'));
    }

    /** @dataProvider onKernelRequestCommandNotExistingProvider */
    public function testOnKernelRequestCommandNotExisting(?string $collectionOperationName): void
    {
        $event = $this->getEvent(['_api_collection_operation_name' => $collectionOperationName]);

        if (null !== $collectionOperationName) {
            $this->descriptionContainer->expects(self::once())
                ->method('has')
                ->with($collectionOperationName)
                ->willReturn(false)
            ;
        }

        $this->listener->onKernelRequest($event);
        self::assertNull($event->getRequest()->attributes->get('_api_resource_class'));
    }

    public function onKernelRequestCommandNotExistingProvider(): array
    {
        return [
            [null],
            ['foo'],
        ];
    }

    public function testOnKernelRequest(): void
    {
        $event = $this->getEvent(['_api_collection_operation_name' => 'foo']);

        $descriptionContainer = new DescriptionContainer(['foo' => [
            'class' => 'Foo\\Bar',
            'payload' => ['some' => 'string'],
        ]]);

        $listener = new CommandResourceClassEventListener($descriptionContainer);
        $listener->onKernelRequest($event);
        self::assertSame('Foo\\Bar', $event->getRequest()->attributes->get('_api_resource_class'));
    }

    private function getEvent(array $attributes = [], string $method = Request::METHOD_POST): RequestEvent
    {
        $request = new Request([], [], $attributes);
        $request->setMethod($method);

        return new RequestEvent($this->createMock(KernelInterface::class), $request, HttpKernelInterface::MASTER_REQUEST);
    }
}
