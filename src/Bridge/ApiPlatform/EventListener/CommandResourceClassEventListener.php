<?php

namespace Botilka\Bridge\ApiPlatform\EventListener;

use ApiPlatform\Core\EventListener\EventPriorities;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class CommandResourceClassEventListener implements EventSubscriberInterface
{
    private $descriptionContainer;

    public function __construct(DescriptionContainerInterface $descriptionContainer)
    {
        $this->descriptionContainer = $descriptionContainer;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $attributes = $request->attributes;
        $collectionOperationName = $attributes->get('_api_collection_operation_name');

        if (Request::METHOD_POST !== $request->getMethod() || null === $collectionOperationName || !$this->descriptionContainer->has($collectionOperationName)) {
            return;
        }

        $resource = $this->descriptionContainer->get($collectionOperationName);

        $attributes->set('_api_resource_class', $resource['class']);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', EventPriorities::PRE_DESERIALIZE],
            ],
        ];
    }
}
