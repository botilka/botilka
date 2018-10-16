<?php

namespace Botilka\Bridge\Symfony\Bundle\DependencyInjection\Compiler;

use Botilka\Bridge\ApiPlatform\Description\DescriptionContainer;
use Botilka\Bridge\ApiPlatform\Identifier\IdentifierGenerator;
use Botilka\Bridge\ApiPlatform\Resource\Query;
use Botilka\Bridge\ApiPlatform\Resource\Command;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ApiPlatformDescriptionContainerPass implements CompilerPassInterface
{
    private const RESOURCE_TO_TAG = [
        Command::class => 'cqrs.command',
        Query::class => 'cqrs.query',
    ];

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(DescriptionContainer::class)) {
            return;
        }
        $container->getDefinition(DescriptionContainer::class)->setAbstract(true);

        foreach (self::RESOURCE_TO_TAG as $className => $tagName) {
            $this->registerDescriptionContainer($container, $className, $tagName);
        }
    }

    private function registerDescriptionContainer(ContainerBuilder $container, string $className, string $tag): void
    {
        $childContainerDefinition = new ChildDefinition(DescriptionContainer::class);

        $serviceIds = $container->findTaggedServiceIds($tag);
        $collection = [];
        foreach ($serviceIds as $serviceId => $tags) {
            $class = new \ReflectionClass($serviceId);
            $payload = $this->extractConstructorArgumentsUntilScalar($class);

            $identifier = IdentifierGenerator::generate($serviceId, $className);
            $collection[$identifier] = ['class' => $serviceId, 'payload' => $payload];
        }
        $childContainerDefinition->setArgument('$data', $collection);
        $container->addDefinitions([$className.'.description_container' => $childContainerDefinition]);
    }

    /**
     * Will recursively navigate in constructor arguments until we have only scalars.
     */
    private function extractConstructorArgumentsUntilScalar(\ReflectionClass $class): array
    {
        $values = [];
        $constructor = $class->getConstructor();
        if (null === $constructor) {
            throw new \LogicException(\sprintf('Class "%s" must have a constructor.', $class->getName()));
        }
        $constructorParameters = $constructor->getParameters();
        foreach ($constructorParameters as $parameter) {
            $class = $parameter->getClass();
            if (null !== $class) {
                $values[$parameter->getName()] = $this->extractConstructorArgumentsUntilScalar($class);
                continue;
            }
            $type = $parameter->allowsNull() ? '?' : '';
            $values[$parameter->getName()] = $type.$parameter->getType()->getName();
        }

        return $values;
    }
}
