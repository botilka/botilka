<?php

namespace Botilka\Bridge\ApiPlatform\EventListener;

use ApiPlatform\Core\EventListener\EventPriorities;
use Botilka\Application\Query\QueryBus;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Hydrator\HydrationException;
use Botilka\Bridge\ApiPlatform\Hydrator\QueryHydratorInterface;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class QueryResourceClassEventListener implements EventSubscriberInterface
{
    private $queryBus;
    private $descriptionContainer;
    private $queryHydrator;

    public function __construct(QueryBus $queryBus, DescriptionContainerInterface $descriptionContainer, QueryHydratorInterface $queryHydrator)
    {
        $this->queryBus = $queryBus;
        $this->descriptionContainer = $descriptionContainer;
        $this->queryHydrator = $queryHydrator;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $attributes = $request->attributes;
        $queryName = $attributes->get('_api_item_operation_name');

        if (Query::class !== $attributes->get('_api_resource_class') || null === $queryName || !$this->descriptionContainer->has($queryName)) {
            return;
        }

        $attributes->set('_api_receive', false); // bypass ReadListener

        $description = $this->descriptionContainer->get($queryName);

        try {
            $query = $this->queryHydrator->hydrate($request->query->all(), $description['class']);
            $result = $this->queryBus->dispatch($query);
            $attributes->set('data', $result);
        } catch (HydrationException $e) {
            $attributes->set('data', $e->getConstraintViolationList());

            return;
        }
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
