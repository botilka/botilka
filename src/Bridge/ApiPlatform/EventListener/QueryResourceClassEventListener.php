<?php

namespace Botilka\Bridge\ApiPlatform\EventListener;

use ApiPlatform\Core\EventListener\EventPriorities;
use Botilka\Application\Query\Query as CQRSQuery;
use Botilka\Application\Query\QueryBus;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class QueryResourceClassEventListener implements EventSubscriberInterface
{
    private $descriptionContainer;
    private $serializer;
    private $queryBus;

    public function __construct(DescriptionContainerInterface $descriptionContainer, DenormalizerInterface $serializer, QueryBus $queryBus)
    {
        $this->descriptionContainer = $descriptionContainer;
        $this->serializer = $serializer;
        $this->queryBus = $queryBus;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $attributes = $request->attributes;

        if (Query::class !== $attributes->get('_api_resource_class') || !$this->descriptionContainer->has($queryName = $attributes->get('_api_item_operation_name', ''))) {
            return;
        }
        $description = $this->descriptionContainer->get($queryName);

        /** @var CQRSQuery $query */
        $query = $this->serializer->denormalize($request->query->all(), $description['class']);

        $result = $this->queryBus->dispatch($query);

        $attributes->set('_api_receive', false); // bypass ReadListener
        $attributes->set('data', $result);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', EventPriorities::PRE_READ],
            ],
        ];
    }
}
