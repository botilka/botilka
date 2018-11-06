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

        if ('api_queries_get_collection' === $attributes->get('_route') || !$this->descriptionContainer->has($attributes->get('_api_collection_operation_name', ''))) {
            return;
        }

        /** @var Query $data */
        $data = $request->attributes->get('data')[0]; // it'a a collection, we need to retrieve the first item

        $description = $this->descriptionContainer->get($data->getName());

        /** @var CQRSQuery $query */
        $query = $this->serializer->denormalize($request->query->all(), $description['class']);

        $result = $this->queryBus->dispatch($query);

        $attributes->set('data', $result);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', EventPriorities::POST_READ],
            ],
        ];
    }
}
