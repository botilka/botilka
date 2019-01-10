<?php

declare(strict_types=1);

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
            throw new \LogicException("Class '{$class->getName()}' must have a constructor.");
        }
        $constructorParameters = $constructor->getParameters();
        foreach ($constructorParameters as $parameter) {

            $parameterClass = $parameter->getClass();

            if (null !== $parameterClass) {

                $parameterName = $parameter->getName();
                if (in_array($parameterClass->getName(), [\DateTime::class, \DateTimeImmutable::class, \DateInterval::class], true)) {
                    $values[$parameterName] = 'string';
                    continue;
                }

                $values[$parameterName] = $this->extractConstructorArgumentsUntilScalar($parameterClass);
                continue;
            }

            $parameterName = $parameter->getName();
            /** @var ?\ReflectionType $parameterType */
            $parameterType = $parameter->getType();
            if (null === $parameterType) {
                throw new \InvalidArgumentException("Parameter '$$parameterName' of class '{$class->getName()}' is not typed. Please type hint all Query & Command parameters.");
            }
            $values[$parameterName] = ($parameter->allowsNull() ? '?' : '').$parameterType->getName();
        }

        return $values;
    }
}
