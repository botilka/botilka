<?php

declare(strict_types=1);

namespace Botilka\Bridge\ApiPlatform\EventListener;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use ApiPlatform\Core\EventListener\EventPriorities;
use Botilka\Application\Query\QueryBus;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Hydrator\QueryHydratorInterface;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class QueryResourceClassEventListener implements EventSubscriberInterface
{
    private $queryBus;
    private $descriptionContainer;
    private $hydrator;

    public function __construct(QueryBus $queryBus, DescriptionContainerInterface $descriptionContainer, QueryHydratorInterface $hydrator)
    {
        $this->queryBus = $queryBus;
        $this->descriptionContainer = $descriptionContainer;
        $this->hydrator = $hydrator;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();
        $attributes = $request->attributes;

        if (Query::class !== $attributes->get('_api_resource_class') || null === ($queryName = $attributes->get('_api_item_operation_name')) || !$this->descriptionContainer->has($queryName)) {
            return;
        }

        $attributes->set('_api_receive', false); // bypass ReadListener

        $description = $this->descriptionContainer->get($queryName);

        try {
            $query = $this->hydrator->hydrate($request->query->all(), $description['class']);
            $result = $this->queryBus->dispatch($query);
            $attributes->set('data', $result);
        } catch (ValidationException $e) {
            // will be serialized by API Platform
            $attributes->set('data', $e->getConstraintViolationList());
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
