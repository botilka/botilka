<?php

namespace Botilka\Bridge\ApiPlatform\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Botilka\Application\Query\QueryBus;
use Botilka\Bridge\ApiPlatform\Description\DescriptionNotFoundException;
use Botilka\Application\Query\Query as CQRSQuery;
use Botilka\Bridge\ApiPlatform\Description\DescriptionContainerInterface;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\SerializerInterface;

final class QueryDataProvider implements CollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $queryBus;
    private $serializer;
    private $descriptionContainer;
    private $requestStack;

    public function __construct(QueryBus $queryBus, SerializerInterface $serializer, DescriptionContainerInterface $descriptionContainer, RequestStack $requestStack)
    {
        $this->queryBus = $queryBus;
        $this->serializer = $serializer;
        $this->descriptionContainer = $descriptionContainer;
        $this->requestStack = $requestStack;
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        $collection = [];
        foreach ($this->descriptionContainer as $id => $description) {
            $collection[] = new Query($id, $description['payload']);
        }

        return $collection;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        try {
            $itemDescription = $this->descriptionContainer->get($id);
        } catch (DescriptionNotFoundException $e) {
            throw new NotFoundHttpException(\sprintf('Query "%s" not found.', $id));
        }
        $request = $this->requestStack->getCurrentRequest();

        $payload = $request->query->all();

        try {
            /** @var CQRSQuery $query */
            $query = $this->serializer->deserialize(\json_encode($payload), $itemDescription['class'], 'json');
        } catch (MissingConstructorArgumentsException $e) {
            throw new BadRequestHttpException(\sprintf('Unable to create query "%s". Please check your parameters.', $id));
        }

        return $this->queryBus->dispatch($query);
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Query::class === $resourceClass;
    }
}
