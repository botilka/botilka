<?php

namespace Botilka\Tests\Bridge\ApiPlatform\EventListener;

use ApiPlatform\Core\EventListener\EventPriorities;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\EventListener\CommandResourceClassEventListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class CommandResourceClassEventListenerTest extends TestCase
{
    /** @var CommandResourceClassEventListener */
    private $listener;
    /** @var DescriptionContainerInterface|MockObject */
    private $descriptionContainer;

    public function setUp()
    {
        $this->descriptionContainer = $this->createMock(DescriptionContainerInterface::class);
        $this->listener = new CommandResourceClassEventListener($this->descriptionContainer);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame([
            KernelEvents::REQUEST => [
                ['onKernelRequest', EventPriorities::PRE_DESERIALIZE],
            ],
        ], CommandResourceClassEventListener::getSubscribedEvents());
    }

    public function testOnKernelRequestNotPost()
    {
        $request = Request::create('http://localhost', Request::METHOD_GET);
        $event = new GetResponseEvent($this->createMock(KernelInterface::class), $request, HttpKernelInterface::MASTER_REQUEST);

        $this->descriptionContainer->expects($this->never())
            ->method('get');

        $this->listener->onKernelRequest($event);
        $this->assertNull($request->attributes->get('_api_resource_class'));
    }

    /** @dataProvider onKernelRequestCommandNotExistingProvider */
    public function testOnKernelRequestCommandNotExisting(?string $collectionOperationName)
    {
        $request = Request::create('http://localhost', Request::METHOD_POST);
        $event = new GetResponseEvent($this->createMock(KernelInterface::class), $request, HttpKernelInterface::MASTER_REQUEST);
        $request->attributes->set('_api_collection_operation_name', $collectionOperationName);

        if (null !== $collectionOperationName) {
            $this->descriptionContainer->expects($this->once())
                ->method('has')
                ->with($collectionOperationName)
                ->willReturn(false);
        }

        $this->listener->onKernelRequest($event);
        $this->assertNull($request->attributes->get('_api_resource_class'));
    }

    public function onKernelRequestCommandNotExistingProvider(): array
    {
        return [
            [null],
            ['foo'],
        ];
    }

    public function testOnKernelRequest()
    {
        $request = Request::create('http://localhost', Request::METHOD_POST);
        $event = new GetResponseEvent($this->createMock(KernelInterface::class), $request, HttpKernelInterface::MASTER_REQUEST);
        $request->attributes->set('_api_collection_operation_name', 'foo');

        $descriptionContainer = new DescriptionContainer(['foo' => [
            'class' => 'Foo\\Bar',
            'payload' => ['some' => 'string'],
        ]]);

        $listener = new CommandResourceClassEventListener($descriptionContainer);
        $listener->onKernelRequest($event);
        $this->assertSame('Foo\\Bar', $request->attributes->get('_api_resource_class'));
    }
}
